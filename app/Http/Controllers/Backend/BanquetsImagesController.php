<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Banquet;
use App\Models\BanquetImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ImageHelper;
use Illuminate\Support\Facades\Log;

class BanquetsImagesController extends Controller
{
    public function createForBanquet($id)
    {
        $banquet = Banquet::findOrFail($id);
        
        $form = '
        <div class="modal-body">
            <form method="POST" action="' . route('manage-banquet-images.store') . '" accept-charset="UTF-8" enctype="multipart/form-data" id="banquetImagesAddForm">
                ' . csrf_field() . '
                <input type="hidden" name="banquets_id" value="' . $banquet->id . '">
                <div class="row">                    
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <strong>Adding images to:</strong> ' . $banquet->title . '
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="banquets_image" class="form-label">Select Banquet Images *</label>
                            <input type="file" id="banquets_image" name="banquets_image[]" class="form-control" multiple accept="image/jpeg,image/png,image/webp" >
                            <div class="invalid-feedback" id="banquets_image_error"></div>
                            <small class="text-muted">You can select multiple images (max 10). Supported formats: JPG, PNG, WEBP. Max size per image: 6MB</small>
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
            'banquets_id' => 'required|exists:banquets,id',
            'banquets_image.*' => 'required|image|mimes:jpg,jpeg,png,webp|max:6144',
            'banquets_image' => 'required|array|min:1|max:10',
        ], [
            'banquets_id.required' => 'Please select a banquet',
            'banquets_id.exists' => 'Selected banquet does not exist',
            'banquets_image.required' => 'Please select at least one image',
            'banquets_image.array' => 'Please select valid images',
            'banquets_image.min' => 'Please select at least one image',
            'banquets_image.max' => 'You can upload maximum 10 images at once',
            'banquets_image.*.required' => 'Image file is required',
            'banquets_image.*.image' => 'The file must be an image',
            'banquets_image.*.mimes' => 'Supported formats: JPG, JPEG, PNG, WEBP',
            'banquets_image.*.max' => 'Each image must not exceed 6MB',
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
            $banquet = Banquet::findOrFail($request->banquets_id);
            $maxOrder = BanquetImage::where('banquets_id', $banquet->id)->max('order') ?? 0;
            if ($request->hasFile('banquets_image')) {
                foreach ($request->file('banquets_image') as $key => $image) {
                    if (!$image->isValid()) {
                        throw new \Exception("File " . ($key + 1) . " is invalid.");
                    }
                    $fileName = 'banquet_' . $banquet->id . '_' . uniqid() . '_' . time() . '.webp';
                    $path = 'banquets/' . $fileName;
                    if (!Storage::disk('public')->exists('banquets')) {
                        Storage::disk('public')->makeDirectory('banquets');
                    }
                    ImageHelper::saveAsWebp($image, storage_path('app/public/' . $path), 75);
                    if (!Storage::disk('public')->exists($path)) {
                        throw new \Exception("Failed to save image file " . ($key + 1));
                    }
                    $uploadedFiles[] = $fileName;
                    BanquetImage::create([
                        'banquets_id' => $banquet->id,
                        'image_file' => $fileName,
                        'order' => ++$maxOrder,
                    ]);
                }
            } else {
                throw new \Exception('No image files provided.');
            }            
            DB::commit();
            $banquetList = Banquet::with(['images' => function($query) {
                $query->orderBy('order')->limit(4);
            }])->paginate(20);            
            return response()->json([
                'status' => true,
                'message' => 'Banquet images added successfully',
                'html' => view('backend.pages.banquets.partials.banquets-list', compact('banquetList'))->render(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            if (!empty($uploadedFiles)) {
                foreach ($uploadedFiles as $fileName) {
                    $filePath = 'banquets/' . $fileName;
                    if (Storage::disk('public')->exists($filePath)) {
                        Storage::disk('public')->delete($filePath);
                    }
                }
            }            
            Log::error('Banquet images upload error: ' . $e->getMessage(), [
                'exception' => $e,
                'banquet_id' => $request->banquets_id ?? null,
                'file_count' => $request->hasFile('banquets_image') ? count($request->file('banquets_image')) : 0
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Upload failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * View all images for a specific banquet
     */
    public function viewBanquetImages($id)
    {
        $banquet = Banquet::with('images')->findOrFail($id);
        $imagesHtml = '
        <div class="modal-body">
            <div class="alert alert-info mb-3">
                <strong>Banquet:</strong> ' . e($banquet->title) . '
                <span class="float-end">
                    Total Images: ' . $banquet->images->count() . '
                </span>
            </div>';

        if ($banquet->images->count() > 0) {
            $imagesHtml .= '
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="20%">Image</th>
                            <th width="15%">Action</th>
                        </tr>
                    </thead>
                    <tbody>';

            foreach ($banquet->images as $key => $image) {
                $imagesHtml .= '
                <tr>
                    <td>' . ($key + 1) . '</td>
                    <td>
                        <img src="' . asset('storage/banquets/' . $image->image_file) . '"
                            class="img-thumbnail"
                            style="max-height: 80px;">
                    </td>
                    <td>
                        <form action="' . route('manage-banquet-images.destroy', $image->id) . '"
                            method="POST"
                            class="d-inline">
                            ' . csrf_field() . '
                            ' . method_field('DELETE') . '
                            <button type="submit"
                                    class="btn btn-sm btn-danger show_confirm_image_delete"
                                    data-name="this image"
                                    title="Delete Image">
                                <i class="fa fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>';
            }

            $imagesHtml .= '
                    </tbody>
                </table>
            </div>';

        } else {

            $imagesHtml .= '
            <div class="text-center py-5">
                <div class="alert alert-warning">
                    <i data-feather="image" class="feather-64"></i>
                    <h5 class="mt-3">No Images Found</h5>
                    <p>This banquet doesn\'t have any images yet.</p>
                </div>
            </div>';
        }

        $imagesHtml .= '
            <div class="modal-footer">
                <button type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">
                    Close
                </button>
            </div>
        </div>';

        return response()->json([
            'status'  => 'success',
            'message' => 'Images loaded successfully',
            'html'    => $imagesHtml,
        ]);
    }

    public function destroyImage($id)
    {
        DB::beginTransaction();
        
        try {
            $image = BanquetImage::findOrFail($id);
            $banquetId = $image->banquets_id;
            $imageFile = $image->image_file;
            $image->delete();
            if ($imageFile && Storage::disk('public')->exists('banquets/' . $imageFile)) {
                Storage::disk('public')->delete('banquets/' . $imageFile);
            }
            $remainingImages = BanquetImage::where('banquets_id', $banquetId)
                ->orderBy('order')
                ->get();            
            $order = 1;
            foreach ($remainingImages as $remainingImage) {
                $remainingImage->update(['order' => $order]);
                $order++;
            }            
            DB::commit();
            $banquetList = Banquet::with(['images' => function($query) {
                $query->orderBy('order')->limit(4);
            }])->paginate(20);            
            return response()->json([
                'status' => true,
                'message' => 'Image deleted successfully',
                'html' => view('backend.pages.banquets.partials.banquets-list', compact('banquetList'))->render(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();            
            Log::error('Banquet image deletion error: ' . $e->getMessage(), [
                'exception' => $e,
                'image_id' => $id
            ]);            
            return response()->json([
                'status' => 'error',
                'message' => 'Delete failed: ' . $e->getMessage(),
            ], 500);
        }
    }
    
}
