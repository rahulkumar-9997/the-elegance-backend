<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Helpers\ImageHelper;
use App\Models\Flyer;
class FlyersController extends Controller
{
    public function index()
    {
        $flyersList = Flyer::orderBy('order')->paginate(20);
        return view('backend.pages.flyers.index', compact('flyersList'));
    }

    public function create(Request $request){
        $form = '
        <div class="modal-body">
            <form method="POST" action="' . route('manage-flyers.store') . '" accept-charset="UTF-8" enctype="multipart/form-data" id="flyersAddForm">
                ' . csrf_field() . '
                <div class="row">                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="flyers_link" class="form-label">Flyers Link *</label>
                            <input type="text" id="flyers_link" name="flyers_link" class="form-control">
                        </div>
                    </div> 
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="flyers_image" class="form-label">Select Flyers Image *</label>
                            <input type="file" id="flyers_image" name="flyers_image" class="form-control">
                        </div>
                    </div>
                    <div class="col-sm-12 col-12">
                        <div class="mb-3 col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="status" name="status" checked>
                                <label class="form-check-label" for="status">Status</label>
                            </div>
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

    public function store(Request $request)
    {   
        $request->merge([
            'status' => $request->has('status') ? 1 : 0
        ]);
        $validator = Validator::make($request->all(), [
            'flyers_link' => 'required|url|max:500',
            'flyers_image' => 'required|image|mimes:jpg,jpeg,png,webp|max:6144',
            'status' => 'sometimes|boolean',
        ], [
            'flyers_link.required' => 'Flyer link is required',
            'flyers_link.url' => 'Please enter a valid URL',
            'flyers_link.max' => 'Link must not exceed 500 characters',
            'flyers_image.required' => 'Please select flyer image',
            'flyers_image.image' => 'The file must be an image',
            'flyers_image.mimes' => 'Supported formats: JPG, JPEG, PNG, WEBP',
            'flyers_image.max' => 'Image size must not exceed 6MB',
            'status.boolean' => 'Status must be true or false',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }
        DB::beginTransaction();
        $fileName = null;        
        try {
            $image = $request->file('flyers_image');
            $fileName = 'flyer_hotel_elegance' . uniqid() . '_' . time() . '.webp';
            $directory = 'flyers';
            $path = $directory . '/' . $fileName;
            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }
            ImageHelper::saveAsWebp(
                $image,
                storage_path('app/public/' . $path),
                75
            );
            if (!Storage::disk('public')->exists($path)) {
                throw new \Exception('Failed to save image file.');
            }
            $flyer = Flyer::create([
                'flyers_link' => $request->flyers_link,
                'image_file' => $fileName,
                'status' => $request->has('status') ? 1 : 0,
                'order' => Flyer::max('order') + 1,
            ]);            
            DB::commit();
            $flyersList = Flyer::orderBy('order')->paginate(20);            
            return response()->json([
                'status' => true,
                'message' => 'Flyer added successfully',
                'html' => view('backend.pages.flyers.partials.flyers-list', compact('flyersList'))->render(),
                'flyer_id' => $flyer->id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            if (!empty($fileName)) {
                $filePath = 'flyers/' . $fileName;
                if (Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }
            }
            Log::error('Flyer store error: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->except('flyers_image')
            ]);            
            return response()->json([
                'status' => false,
                'message' => 'Upload failed. Please try again.',
                'debug' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function edit(Request $request, $id){
        $flyer = Flyer::findOrfail($id);
        $imagePreview = '';
        if ($flyer->image_file) {
            $imageUrl = asset('storage/flyers/' . $flyer->image_file);
            $imagePreview = '<div class="mb-2">
                <label class="form-label">Current Image:</label><br>
                <img src="' . $imageUrl . '" class="img-thumbnail" width="100" alt="Current Image">
            </div>';
        }        
        $form = '
        <div class="modal-body">
            <form method="POST" action="' . route('manage-flyers.update', $flyer->id) . '" accept-charset="UTF-8" enctype="multipart/form-data" id="flyersEditForm">
                ' . csrf_field() . '
                ' . method_field('PUT') . '
                <div class="row">                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="flyers_link" class="form-label">Flyers Link *</label>
                            <input type="text" id="flyers_link" name="flyers_link" class="form-control" value="' . htmlspecialchars($flyer->flyers_link ?? '') . '">
                            <div class="invalid-feedback" id="flyers_link_error"></div>
                        </div>
                    </div> 
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="flyers_image" class="form-label">Flyers Image</label>
                            ' . $imagePreview . '
                            <input type="file" id="flyers_image" name="flyers_image" class="form-control" accept="image/jpeg,image/png,image/webp">
                            <small class="text-muted">Leave empty to keep current image. Max size: 6MB. Supported formats: JPG, PNG, WEBP</small>
                            <div class="invalid-feedback" id="flyers_image_error"></div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-12">
                        <div class="mb-3 col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="status" name="status" value="1" ' . ($flyer->status == 1 ? 'checked' : '') . '>
                                <label class="form-check-label" for="status">Status</label>
                            </div>
                        </div>
                    </div>               
                                
                    <div class="modal-footer pb-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">Update</button>
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

    public function update(Request $request, $id)
    {   
        $flyer = Flyer::findOrFail($id);        
        $validator = Validator::make($request->all(), [
            'flyers_link' => 'required|url|max:500',
            'flyers_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:6144',
            'status' => 'sometimes|boolean',
        ], [
            'flyers_link.required' => 'Flyer link is required',
            'flyers_link.url' => 'Please enter a valid URL',
            'flyers_link.max' => 'Link must not exceed 500 characters',
            'flyers_image.image' => 'The file must be an image',
            'flyers_image.mimes' => 'Supported formats: JPG, JPEG, PNG, WEBP',
            'flyers_image.max' => 'Image size must not exceed 6MB',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }
        DB::beginTransaction();
        $oldFileName = $flyer->image_file;
        $newFileName = null;
        
        try {
            if ($request->hasFile('flyers_image')) {
                $image = $request->file('flyers_image');
                $newFileName = 'flyer_' . uniqid() . '_' . time() . '.webp';
                $path = 'flyers/' . $newFileName;
                if (!Storage::disk('public')->exists('flyers')) {
                    Storage::disk('public')->makeDirectory('flyers');
                }
                ImageHelper::saveAsWebp($image, storage_path('app/public/' . $path), 75);
                if ($oldFileName && Storage::disk('public')->exists('flyers/' . $oldFileName)) {
                    Storage::disk('public')->delete('flyers/' . $oldFileName);
                }                
                $imageFile = $newFileName;
            } else {
                $imageFile = $oldFileName;
            }
            $flyer->update([
                'flyers_link' => $request->flyers_link,
                'image_file' => $imageFile,
                'status' => $request->boolean('status'),
            ]);            
            DB::commit();
            $flyersList = Flyer::orderBy('order')->paginate(20);            
            return response()->json([
                'status' => true,
                'message' => 'Flyer updated successfully',
                'html' => view('backend.pages.flyers.partials.flyers-list', compact('flyersList'))->render(),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            if (!empty($newFileName) && Storage::disk('public')->exists('flyers/' . $newFileName)) {
                Storage::disk('public')->delete('flyers/' . $newFileName);
            }            
            Log::error('Flyer update error: ' . $e->getMessage(), [
                'exception' => $e,
                'flyer_id' => $id,
                'request_data' => $request->except('flyers_image')
            ]);            
            return response()->json([
                'status' => 'error',
                'message' => 'Update failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();        
        try {
            $flyer = Flyer::findOrFail($id);
            $imageFile = $flyer->image_file;  
            $flyer->delete();
            if ($imageFile && Storage::disk('public')->exists('flyers/' . $imageFile)) {
                Storage::disk('public')->delete('flyers/' . $imageFile);
            }
            $remainingFlyers = Flyer::orderBy('order')->get();
            $order = 1;
            foreach ($remainingFlyers as $remainingFlyer) {
                $remainingFlyer->update(['order' => $order]);
                $order++;
            }            
            DB::commit();
            $flyersList = Flyer::orderBy('order')->paginate(20);            
            return response()->json([
                'status' => true,
                'message' => 'Flyer deleted successfully',
                'html' => view('backend.pages.flyers.partials.flyers-list', compact('flyersList'))->render(),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();            
            Log::error('Flyer deletion error: ' . $e->getMessage(), [
                'exception' => $e,
                'flyer_id' => $id
            ]);            
            return response()->json([
                'status' => 'error',
                'message' => 'Delete failed: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function orderUp($id)
    {
        try {
            $flyer = Flyer::findOrFail($id);
            $previousFlyer = Flyer::where('order', '<', $flyer->order)
                ->orderBy('order', 'desc')
                ->first();                
            if ($previousFlyer) {
                $tempOrder = $flyer->order;
                $flyer->order = $previousFlyer->order;
                $previousFlyer->order = $tempOrder;                
                $flyer->save();
                $previousFlyer->save();                
                return redirect()->back()->with('success', 'Order updated successfully.');
            }            
            return redirect()->back()->with('info', 'Already at the top.');            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update order: ' . $e->getMessage());
        }
    }

    public function orderDown($id)
    {
        try {
            $flyer = Flyer::findOrFail($id);
            $nextFlyer = Flyer::where('order', '>', $flyer->order)
                ->orderBy('order', 'asc')
                ->first();                
            if ($nextFlyer) {
                $tempOrder = $flyer->order;
                $flyer->order = $nextFlyer->order;
                $nextFlyer->order = $tempOrder;                
                $flyer->save();
                $nextFlyer->save();                
                return redirect()->back()->with('success', 'Order updated successfully.');
            }            
            return redirect()->back()->with('info', 'Already at the bottom.');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update order: ' . $e->getMessage());
        }
    }


}
