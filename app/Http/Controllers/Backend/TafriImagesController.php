<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Helpers\ImageHelper;
use App\Models\TafriLoungeImage;

class TafriImagesController extends Controller
{
    public function index()
    {
        $tafriImageList = TafriLoungeImage::orderBy('order')->paginate(20);
        return view('backend.pages.tafri-image.index', compact('tafriImageList'));
    }

    public function create(Request $request)
    {
        $form = '
        <div class="modal-body">
            <form method="POST" action="' . route('manage-tafri-lounge-image.store') . '" accept-charset="UTF-8" enctype="multipart/form-data" id="tafriImageAddForm">
                ' . csrf_field() . '
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="tafri_image" class="form-label">Select Tafri Images *</label>
                            <input type="file" id="tafri_image" name="tafri_image[]" class="form-control" multiple accept="image/jpeg,image/png,image/webp">
                            <div class="invalid-feedback" id="tafri_image_error"></div>
                            <small class="text-muted">You can select multiple images. Supported formats: JPG, PNG, WEBP. Max size per image: 6MB</small>
                            <!-- Image preview container -->
                            <div id="image-preview-container" class="mt-2"></div>
                        </div>
                    </div>
                                
                    <div class="modal-footer pb-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Upload Images</button>
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
        $validator = Validator::make($request->all(), [
            'tafri_image.*' => 'required|image|mimes:jpg,jpeg,png,webp|max:6144',
            'tafri_image' => 'required|array|min:1',
        ], [
            'tafri_image.required' => 'Please select at least one image',
            'tafri_image.array' => 'Please select valid images',
            'tafri_image.min' => 'Please select at least one image',
            'tafri_image.*.required' => 'Image file is required',
            'tafri_image.*.image' => 'The file must be an image',
            'tafri_image.*.mimes' => 'Supported formats: JPG, JPEG, PNG, WEBP',
            'tafri_image.*.max' => 'Each image must not exceed 6MB',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        $uploadedFiles = [];

        try {
            $maxOrder = TafriLoungeImage::max('order') ?? 0;
            if ($request->hasFile('tafri_image')) {
                foreach ($request->file('tafri_image') as $key => $image) {
                    if (!$image->isValid()) {
                        throw new \Exception("File " . ($key + 1) . " is invalid.");
                    }
                    $fileName = 'tafri_' . uniqid() . '_' . time() . '.webp';
                    $directory = 'tafri';
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
                        throw new \Exception("Failed to save image file " . ($key + 1));
                    }
                    $uploadedFiles[] = $fileName;
                    $tafriImage = TafriLoungeImage::create([
                        'title' => 'Tafri Image ' . ($maxOrder + $key + 1),
                        'image_file' => $fileName,
                        'order' => ++$maxOrder,
                    ]);
                }
            } else {
                throw new \Exception('No image files provided.');
            }
            DB::commit();
            $tafriImageList = TafriLoungeImage::orderBy('order')->paginate(20);
            return response()->json([
                'status' => true,
                'message' => 'Tafri images added successfully',
                'html' => view('backend.pages.tafri-image.partials.image-list', compact('tafriImageList'))->render(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            if (!empty($uploadedFiles)) {
                foreach ($uploadedFiles as $fileName) {
                    $filePath = 'tafri/' . $fileName;
                    if (Storage::disk('public')->exists($filePath)) {
                        Storage::disk('public')->delete($filePath);
                    }
                }
            }
            Log::error('Tafri image upload error: ' . $e->getMessage(), [
                'exception' => $e,
                'file_count' => $request->hasFile('tafri_image') ? count($request->file('tafri_image')) : 0
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Upload failed. Please try again.',
                'debug' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function edit($id)
    {
        $tafriImage = TafriLoungeImage::findOrFail($id);

        $form = '
        <div class="modal-body">
            <form method="POST" action="' . route('manage-tafri-lounge-image.update', $tafriImage->id) . '" accept-charset="UTF-8" enctype="multipart/form-data" id="tafriImageEditForm">
                ' . csrf_field() . '
                ' . method_field('PUT') . '
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title *</label>
                            <input type="text" id="title" name="title" class="form-control" value="' . htmlspecialchars($tafriImage->title) . '">
                            <div class="invalid-feedback" id="title_error"></div>
                        </div>
                    </div>
                    
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label">Current Image:</label><br>
                            <img src="' . asset('storage/tafri/' . $tafriImage->image_file) . '" 
                                 class="img-thumbnail mb-2" 
                                 width="150" 
                                 alt="Current Image">
                        </div>
                    </div>
                    
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="tafri_image" class="form-label">Change Image (Optional)</label>
                            <input type="file" id="tafri_image" name="tafri_image" class="form-control" accept="image/jpeg,image/png,image/webp">
                            <div class="invalid-feedback" id="tafri_image_error"></div>
                            <small class="text-muted">Leave empty to keep current image. Supported formats: JPG, PNG, WEBP. Max size: 6MB</small>
                        </div>
                    </div>
                    
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="order" class="form-label">Order *</label>
                            <input type="number" id="order" name="order" class="form-control" value="' . $tafriImage->order . '" min="1" required>
                            <div class="invalid-feedback" id="order_error"></div>
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
            'message' => 'Form created successfully',
            'form' => $form,
        ]);
    }

    public function update(Request $request, $id)
    {
        $tafriImage = TafriLoungeImage::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'tafri_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:6144',
            'order' => 'required|integer|min:1',
        ], [
            'title.required' => 'Title is required',
            'title.string' => 'Title must be a valid string',
            'title.max' => 'Title must not exceed 255 characters',
            'tafri_image.image' => 'The file must be an image',
            'tafri_image.mimes' => 'Supported formats: JPG, JPEG, PNG, WEBP',
            'tafri_image.max' => 'Image size must not exceed 6MB',
            'order.required' => 'Order is required',
            'order.integer' => 'Order must be a number',
            'order.min' => 'Order must be at least 1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        $oldFileName = $tafriImage->image_file;
        $newFileName = null;
        try {
            if ($request->hasFile('tafri_image')) {
                $image = $request->file('tafri_image');
                $newFileName = 'tafri_' . uniqid() . '_' . time() . '.webp';
                $directory = 'tafri';
                $path = $directory . '/' . $newFileName;
                if (!Storage::disk('public')->exists($directory)) {
                    Storage::disk('public')->makeDirectory($directory);
                }
                ImageHelper::saveAsWebp(
                    $image,
                    storage_path('app/public/' . $path),
                    75
                );
                if (!Storage::disk('public')->exists($path)) {
                    throw new \Exception('Failed to save new image file.');
                }
                if ($oldFileName && Storage::disk('public')->exists('tafri/' . $oldFileName)) {
                    Storage::disk('public')->delete('tafri/' . $oldFileName);
                }
                $imageFile = $newFileName;
            } else {
                $imageFile = $oldFileName;
            }
            if ($request->order != $tafriImage->order) {
                $allImages = TafriLoungeImage::where('id', '!=', $id)
                    ->orderBy('order')
                    ->get();
                $newOrder = $request->order;
                $adjustedImages = [];

                foreach ($allImages as $image) {
                    if ($image->order >= $newOrder) {
                        $image->order = $image->order + 1;
                        $adjustedImages[] = $image;
                    }
                }
                foreach ($adjustedImages as $adjustedImage) {
                    $adjustedImage->save();
                }
            }
            $tafriImage->update([
                'title' => $request->title,
                'image_file' => $imageFile,
                'order' => $request->order,
            ]);
            DB::commit();
            $tafriImageList = TafriLoungeImage::orderBy('order')->paginate(20);
            return response()->json([
                'status' => true,
                'message' => 'Tafri image updated successfully',
                'html' => view('backend.pages.tafri-image.partials.image-list', compact('tafriImageList'))->render(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            if (!empty($newFileName) && Storage::disk('public')->exists('tafri/' . $newFileName)) {
                Storage::disk('public')->delete('tafri/' . $newFileName);
            }
            Log::error('Tafri image update error: ' . $e->getMessage(), [
                'exception' => $e,
                'tafri_image_id' => $id,
                'request_data' => $request->except('tafri_image')
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Update failed. Please try again.',
                'debug' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $tafriImage = TafriLoungeImage::findOrFail($id);
            $imageFile = $tafriImage->image_file;
            $tafriImage->delete();
            if ($imageFile && Storage::disk('public')->exists('tafri/' . $imageFile)) {
                Storage::disk('public')->delete('tafri/' . $imageFile);
            }
            $remainingImages = TafriLoungeImage::orderBy('order')->get();
            $order = 1;
            foreach ($remainingImages as $remainingImage) {
                $remainingImage->update(['order' => $order]);
                $order++;
            }
            DB::commit();
            $tafriImageList = TafriLoungeImage::orderBy('order')->paginate(20);
            return response()->json([
                'status' => true,
                'message' => 'Tafri image deleted successfully',
                'html' => view('backend.pages.tafri-image.partials.image-list', compact('tafriImageList'))->render(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Tafri image deletion error: ' . $e->getMessage(), [
                'exception' => $e,
                'tafri_image_id' => $id
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Delete failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function orderUp($id)
    {
        try {
            $image = TafriLoungeImage::findOrFail($id);
            $previousImage = TafriLoungeImage::where('order', '<', $image->order)
                ->orderBy('order', 'desc')
                ->first();
            if ($previousImage) {
                $tempOrder = $image->order;
                $image->order = $previousImage->order;
                $previousImage->order = $tempOrder;

                $image->save();
                $previousImage->save();
            }

            return redirect()->back()->with('success', 'Order updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update order: ' . $e->getMessage());
        }
    }

    public function orderDown($id)
    {
        try {
            $image = TafriLoungeImage::findOrFail($id);
            $nextImage = TafriLoungeImage::where('order', '>', $image->order)
                ->orderBy('order', 'asc')
                ->first();
            if ($nextImage) {
                $tempOrder = $image->order;
                $image->order = $nextImage->order;
                $nextImage->order = $tempOrder;
                $image->save();
                $nextImage->save();
            }
            return redirect()->back()->with('success', 'Order updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update order: ' . $e->getMessage());
        }
    }
}
