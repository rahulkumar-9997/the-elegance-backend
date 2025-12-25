<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;
use App\Models\Testimonial;

class TestimonialController extends Controller
{
    public function index(){ 
        $testimonialsList = Testimonial::orderBy('id', 'desc')->get(); 
        //return response()->json($testimonialsList);
        return view('backend.pages.testimonials.index', compact('testimonialsList'));
    }

    public function create(Request $request){
        $action = $request->input('action');        
        $form = '
        <div class="modal-body">
            <form method="POST" action="' . route('manage-testimonials.store') . '" accept-charset="UTF-8" enctype="multipart/form-data" id="testimonialsAddForm">
                ' .csrf_field() . '
                <div class="row">
                    <div class="col-md-5">
                        <div class="mb-3">
                            <label for="testimonial_name" class="form-label">Testimonial Name *</label>
                            <input type="text" id="testimonial_name" name="testimonial_name" class="form-control">
                        </div>
                    </div>
                    
                    <div class="col-md-5">
                        <div class="mb-3">
                            <label for="profile_pic" class="form-label">Profile Pic </label>
                            <input type="file" id="profile_pic" name="profile_pic" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3 col-md-2 mt-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="status" name="status" value="1" checked="">
                            <label class="form-check-label" for="status">Status</label>
                        </div>
                    </div>
                     <div class="col-md-12">
                        <div class="mb-3">
                            <label for="content" class="form-label">Content *</label>
                            <textarea class="form-control" id="content" rows="4" name="content"></textarea>
                            <div class="invalid-feedback" id="short_content_error"></div>
                        </div>
                    </div>            
                    <div class="modal-footer pb-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </div>
            </form>
        </div>
        ';
        return response()->json([
            'message' => 'Form created successfully',
            'form' => $form,
        ]);
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'testimonial_name' => 'required|string|max:255',
            'content' => 'required|string',          
            'profile_pic' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:6144',
            'status' => 'sometimes|boolean'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        DB::beginTransaction();
        try {
            $fileName = null;
            $destinationPath = public_path('upload/testimonials');            
            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true);
            }
            if ($request->hasFile('profile_pic')) {
                $file = $request->file('profile_pic');
                $safeTitle = Str::slug($request->testimonial_name);
                $uniqueTimestamp = round(microtime(true) * 1000);
                $extension = 'webp'; 
                $fileName = $safeTitle.'-'.$uniqueTimestamp.'.'.$extension;
                $filePath = $destinationPath.'/'.$fileName;                
                $image = Image::make($file);
                $image->encode('webp', 75);
                $image->save($filePath);
            }
            $testimonial = Testimonial::create([
                'title' => $request->testimonial_name,
                'image' => $fileName,
                'content' => $request->content,             
                'status' => $request->has('status') ? 1 : 0
            ]);
            
            DB::commit();    
            $testimonialsList = Testimonial::orderBy('id', 'desc')->get();
            return response()->json([
                'status' => 'success',
                'message' => 'Testimonial created successfully!',
                'testimonialsListData' => view('backend.pages.testimonials.partials.testimonials-list', compact('testimonialsList'))->render()
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($fileName) && File::exists($destinationPath.'/'.$fileName)) {
                File::delete($destinationPath.'/'.$fileName);
            }
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create testimonial: '.$e->getMessage()
            ], 500);
        }
    }

    public function edit(Request $request, $id){
        $testimonial = Testimonial::findOrFail($id);       
        $form = '
        <div class="modal-body">
            <form method="POST" action="' . route('manage-testimonials.update', $testimonial->id) . '" accept-charset="UTF-8" enctype="multipart/form-data" id="testimonialsEditForm">
                ' . csrf_field() . '
                ' . method_field('PUT') . '
                <div class="row">
                    <div class="col-md-5">
                        <div class="mb-3">
                            <label for="testimonial_name" class="form-label">Testimonial Name *</label>
                            <input type="text" id="testimonial_name" name="testimonial_name" class="form-control" value="' . htmlspecialchars($testimonial->title) . '">
                        </div>
                    </div>
                    
                    <div class="col-md-5">
                        <div class="mb-3">
                            <label for="profile_pic" class="form-label">Profile Pic</label>
                            <input type="file" id="profile_pic" name="profile_pic" class="form-control">
                            ' . ($testimonial->image ? '<div class="mt-2"><img src="' . asset('upload/testimonials/' . $testimonial->image) . '" width="100"></div>' : '') . '
                        </div>
                    </div>
                   <div class="mb-3 col-md-2 mt-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="status" name="status" value="1" ' . ($testimonial->status ? 'checked' : '') . '>
                            <label class="form-check-label" for="status">Status</label>
                        </div>
                    </div> 
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="content" class="form-label">Content *</label>
                            <textarea class="form-control" id="content" rows="4" name="content">' . htmlspecialchars($testimonial->content) . '</textarea>
                            <div class="invalid-feedback" id="short_content_error"></div>
                        </div>
                    </div>                                                    
                                       
                    <div class="modal-footer pb-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </div>
            </form>
        </div>
        ';
        
        return response()->json([
            'message' => 'Form created successfully',
            'form' => $form,
        ]);
    }

    public function update(Request $request, $id){
        $validator = Validator::make($request->all(), [
            'testimonial_name' => 'required|string|max:255',
            'content' => 'required|string',            
            'profile_pic' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:6144',
            'status' => 'sometimes|boolean'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        DB::beginTransaction();
        try {
            $testimonial = Testimonial::findOrFail($id);
            $destinationPath = public_path('upload/testimonials');            
            $updateData = [
                'title' => $request->testimonial_name,
                'content' => $request->content,
                'status' => $request->has('status') ? 1 : 0
            ];
            if ($request->hasFile('profile_pic')) {
                if ($testimonial->image && File::exists($destinationPath.'/'.$testimonial->image)) {
                    File::delete($destinationPath.'/'.$testimonial->image);
                }
                $file = $request->file('profile_pic');
                $safeTitle = Str::slug($request->testimonial_name);
                $uniqueTimestamp = round(microtime(true) * 1000);
                $extension = 'webp';
                $fileName = $safeTitle.'-'.$uniqueTimestamp.'.'.$extension;
                $filePath = $destinationPath.'/'.$fileName;                
                $image = Image::make($file);
                $image->encode('webp', 75);
                $image->save($filePath);                
                $updateData['image'] = $fileName;
            }

            $testimonial->update($updateData);
            DB::commit();
            $testimonialsList = Testimonial::orderBy('id', 'desc')->get();
            return response()->json([
                'status' => 'success',
                'message' => 'Testimonial updated successfully!',
                'testimonialsListData' => view('backend.pages.testimonials.partials.testimonials-list', compact('testimonialsList'))->render()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update testimonial: '.$e->getMessage()
            ], 500);
        }
    }

    public function destroy($id){
        DB::beginTransaction();
        try {
            $testimonial = Testimonial::findOrFail($id);
            $destinationPath = public_path('upload/testimonials');
            if ($testimonial->image && File::exists($destinationPath.'/'.$testimonial->image)) {
                File::delete($destinationPath.'/'.$testimonial->image);
            }
            $testimonial->delete();            
            DB::commit();            
            $testimonialsList = Testimonial::orderBy('id', 'desc')->get();
            return response()->json([
                'status' => 'success',
                'message' => 'Testimonial deleted successfully!',
                'testimonialsListData' => view('backend.pages.testimonials.partials.testimonials-list', compact('testimonialsList'))->render()
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete testimonial: '.$e->getMessage()
            ], 500);
        }
    }
}
