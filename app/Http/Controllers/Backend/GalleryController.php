<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;
use App\Models\Gallery;
class GalleryController extends Controller
{
    public function index(){ 
        $galleryList = Gallery::orderBy('id', 'desc')->paginate(20); 
        //return response()->json($testimonialsList);
        return view('backend.pages.gallery.index', compact('galleryList'));
    }

    public function create(Request $request){
        $form = '
        <div class="modal-body">
            <form method="POST" action="' . route('manage-gallery.store') . '" accept-charset="UTF-8" enctype="multipart/form-data" id="galleryAddForm">
                ' . csrf_field() . '
                <div class="row">                    
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="gallery" class="form-label">Select Multiple Image File (Minimum 20 files required)</label>
                            <input type="file" id="gallery" name="gallery[]" multiple class="form-control">
                            <div class="form-text">Please select at least 20 images</div>
                        </div>
                    </div>                   
                                
                    <div class="modal-footer pb-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </div>
            </form>
        </div>';

        return response()->json([
            'status' => 'success',
            'message' => 'Form created successfully',
            'form' => $form,
        ]);
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'gallery' => 'required|array|min:1',
            'gallery.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:6144',
        ], [
            'gallery.min' => 'Please upload at least 1 images',
            'gallery.*.image' => 'Each file must be an image',
            'gallery.*.max' => 'Each image must be less than 6MB',
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
            $destinationPath = public_path('upload/gallery');
            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true);
            }

            $uploadedImages = [];
            
            foreach ($request->file('gallery') as $file) {
                $safeTitle = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
                $uniqueTimestamp = round(microtime(true) * 1000);
                $extension = 'webp';
                $fileName = 'gallery-'.$uniqueTimestamp.'.'.$extension;
                $filePath = $destinationPath.'/'.$fileName;                
                $image = Image::make($file);
                $image->encode('webp', 75);
                $image->save($filePath);
                $gallery = Gallery::create([
                    'title' => $safeTitle,
                    'image' => $fileName,
                    'order' => Gallery::max('order') + 1,
                    'status' => 1 
                ]);
                $uploadedImages[] = $gallery;      
            }

            DB::commit();
            
            $galleryList = Gallery::orderBy('id', 'desc')->paginate(20); 
            return response()->json([
                'status' => 'success',
                'message' => 'Gallery images uploaded successfully!',
                'galleryListData' => view('backend.pages.gallery.partials.gallery-list', compact('galleryList'))->render(),
                'uploadedCount' => count($uploadedImages)
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            if (!empty($uploadedImages)) {
                foreach ($uploadedImages as $gallery) {
                    if (File::exists($destinationPath.'/'.$gallery->image)) {
                        File::delete($destinationPath.'/'.$gallery->image);
                    }
                }
            }
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to upload gallery images: '.$e->getMessage()
            ], 500);
        }
    }

    public function edit(Request $request, $id){
        $gallery = Gallery::findOrFail($id);        
        $form = '
        <div class="modal-body">
            <form method="POST" action="'.route('manage-gallery.update', $gallery->id).'" accept-charset="UTF-8" enctype="multipart/form-data" id="galleryEditForm">
                '.csrf_field().'
                '.method_field('PUT').'
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" value="'.htmlspecialchars($gallery->title).'">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="image" class="form-label">Change Image (Leave blank to keep current)</label>
                            <input type="file" class="form-control" id="image" name="image">
                            <img src="'.asset('upload/gallery/'.$gallery->image).'" width="150" class="img-thumbnail mb-2">
                        </div>
                    </div>
                    
                    <div class="col-md-12">
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="status" name="status" value="1" '.($gallery->status == 1 ? 'checked' : '').'>
                            <label class="form-check-label" for="status">Active</label>
                        </div>
                    </div>
                    
                    <div class="modal-footer pb-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </div>
            </form>
        </div>';

        return response()->json([
            'status' => 'success',
            'message' => 'Edit form loaded successfully',
            'form' => $form
        ]);
    }

    public function update(Request $request, $id){
        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:6144',
            'status' => 'nullable|boolean'
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
            $gallery = Gallery::findOrFail($id);
            $fileName = $gallery->image;
            $destinationPath = public_path('upload/gallery');

            if ($request->hasFile('image')) {
                if ($gallery->image && File::exists($destinationPath.'/'.$gallery->image)) {
                    File::delete($destinationPath.'/'.$gallery->image);
                }
                $file = $request->file('image');
                $safeTitle = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
                $uniqueTimestamp = round(microtime(true) * 1000);
                $extension = 'webp';
                $fileName = $safeTitle.'-'.$uniqueTimestamp.'.'.$extension;
                $filePath = $destinationPath.'/'.$fileName;
                
                $image = Image::make($file);
                $image->encode('webp', 75);
                $image->save($filePath);
            }

            $gallery->update([
                'title' => $request->title,
                'image' => $fileName,
                'status' => $request->has('status') ? 1 : 0
            ]);
            DB::commit();            
            $galleryList = Gallery::orderBy('id', 'desc')->paginate(20); 
            return response()->json([
                'status' => 'success',
                'message' => 'Gallery item updated successfully!',
                'galleryListData' => view('backend.pages.gallery.partials.gallery-list', compact('galleryList'))->render()
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update gallery item: '.$e->getMessage()
            ], 500);
        }
    }

    public function destroy($id){
        DB::beginTransaction();
        try {
            $gallery = Gallery::findOrFail($id);
            $imagePath = public_path('upload/gallery/'.$gallery->image);
            if (File::exists($imagePath)) {
                File::delete($imagePath);
            }
            $gallery->delete();            
            DB::commit();            
            $galleryList = Gallery::orderBy('id', 'desc')->paginate(20); 
            return response()->json([
                'status' => 'success',
                'message' => 'Gallery item deleted successfully!',
                'galleryListData' => view('backend.pages.gallery.partials.gallery-list', compact('galleryList'))->render()
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete gallery item: '.$e->getMessage()
            ], 500);
        }
    }
}
