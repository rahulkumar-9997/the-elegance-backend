<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Helpers\ImageHelper;
use App\Models\Facility;

class FacilitiesController extends Controller
{
    public function index()
    {
        $facilityList = Facility::orderBy('id', 'desc')->paginate(20);
        return view('backend.pages.facilities.index', compact('facilityList'));
    }

    public function create(Request $request)
    {
        $form = '
        <div class="modal-body">
            <form method="POST" action="' . route('manage-facilities.store') . '" id="facilitiesAddForm" enctype="multipart/form-data">
                ' . csrf_field() . '
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="title" class="form-control" placeholder="e.g., Swimming Pool">
                        </div>
                    </div>
                    
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label">Short Description</label>
                            <textarea name="short_desc" rows="3" class="form-control" placeholder="Brief description of the facility" id="short_desc"></textarea>
                        </div>
                    </div>
                    
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label">Facility Image <span class="text-danger">*</span></label>
                            <input type="file" id="image" name="image" class="form-control" accept="image/jpeg,image/png,image/webp">
                            <div class="invalid-feedback" id="image_error"></div>
                            <small class="text-muted">Supported formats: JPG, PNG, WEBP. Max size: 6MB</small>
                            <div id="image-preview-container" class="mt-2"></div>
                        </div>
                    </div>
                    
                    <div class="modal-footer pb-0 mt-3">
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
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'short_desc' => 'nullable|string|max:500',
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:6144',
        ], [
            'title.required' => 'Title is required',
            'title.string' => 'Title must be a valid string',
            'title.max' => 'Title must not exceed 255 characters',
            'short_desc.string' => 'Description must be a valid text',
            'short_desc.max' => 'Description must not exceed 500 characters',
            'image.required' => 'Facility image is required',
            'image.image' => 'The file must be an image',
            'image.mimes' => 'Supported formats: JPG, JPEG, PNG, WEBP',
            'image.max' => 'Image size must not exceed 6MB',
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
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $fileName = 'facility_' . uniqid() . '_' . time() . '.webp';
                $directory = 'facilities';
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
            } else {
                throw new \Exception('No image file provided.');
            }
            $facility = Facility::create([
                'title' => $request->title,
                'short_desc' => $request->short_desc,
                'image' => $fileName,
            ]);
            DB::commit();
            $facilityList = Facility::orderBy('id', 'desc')->paginate(20);
            return response()->json([
                'status' => true,
                'message' => 'Facility added successfully',
                'html' => view(
                    'backend.pages.facilities.partials.facilities-list',
                    compact('facilityList')
                )->render(),
                'facility_id' => $facility->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            if (!empty($fileName)) {
                $filePath = 'facilities/' . $fileName;
                if (Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }
            }
            Log::error('Facility store error: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->except('image')
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
        $facility = Facility::findOrFail($id);
        $imageUrl = '';
        if ($facility->image) {
            $imageUrl = asset('storage/facilities/' . $facility->image);
        }

        $form = '
        <div class="modal-body">
            <form method="POST" action="' . route('manage-facilities.update', $facility->id) . '" id="facilitiesEditForm" enctype="multipart/form-data">
                ' . csrf_field() . '
                ' . method_field('PUT') . '
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" value="' . htmlspecialchars($facility->title) . '" id="title">
                        </div>
                    </div>
                    
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label">Short Description</label>
                            <textarea name="short_desc" rows="3" class="form-control" id="short_desc">' . htmlspecialchars($facility->short_desc) . '</textarea>
                        </div>
                    </div>
                    
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label">Current Image:</label><br>';
                            if ($imageUrl) {
                                $form .= '<img src="' . $imageUrl . '" class="img-thumbnail mb-2" width="150" alt="Current Image"><br>';
                            }
                            $form .= '
                            <label class="form-label">Change Image (Optional)</label>
                            <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp" id="image">
                            <div class="invalid-feedback" id="image_error"></div>
                            <small class="text-muted">Leave empty to keep current image. Supported formats: JPG, PNG, WEBP. Max size: 6MB</small>
                        </div>
                    </div>
                    
                    <div class="modal-footer pb-0 mt-3">
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
        $facility = Facility::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'short_desc' => 'nullable|string|max:500',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:6144',
        ], [
            'title.required' => 'Title is required',
            'title.string' => 'Title must be a valid string',
            'title.max' => 'Title must not exceed 255 characters',
            'short_desc.string' => 'Description must be a valid text',
            'short_desc.max' => 'Description must not exceed 500 characters',
            'image.image' => 'The file must be an image',
            'image.mimes' => 'Supported formats: JPG, JPEG, PNG, WEBP',
            'image.max' => 'Image size must not exceed 6MB',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }
        DB::beginTransaction();
        $oldFileName = $facility->image;
        $newFileName = null;
        try {
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $newFileName = 'facility_' . uniqid() . '_' . time() . '.webp';
                $directory = 'facilities';
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
                if ($oldFileName && Storage::disk('public')->exists('facilities/' . $oldFileName)) {
                    Storage::disk('public')->delete('facilities/' . $oldFileName);
                }
                $imageFile = $newFileName;
            } else {
                $imageFile = $oldFileName;
            }
            $facility->update([
                'title' => $request->title,
                'short_desc' => $request->short_desc,
                'image' => $imageFile,
            ]);
            DB::commit();
            $facilityList = Facility::orderBy('id', 'desc')->paginate(20);
            return response()->json([
                'status' => true,
                'message' => 'Facility updated successfully',
                'html' => view(
                    'backend.pages.facilities.partials.facilities-list',
                    compact('facilityList')
                )->render(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            if (!empty($newFileName) && Storage::disk('public')->exists('facilities/' . $newFileName)) {
                Storage::disk('public')->delete('facilities/' . $newFileName);
            }
            Log::error('Facility update error: ' . $e->getMessage(), [
                'exception' => $e,
                'facility_id' => $id,
                'request_data' => $request->except('image')
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
            $facility = Facility::findOrFail($id);
            $imageFile = $facility->image;
            $facility->delete();
            if ($imageFile && Storage::disk('public')->exists('facilities/' . $imageFile)) {
                Storage::disk('public')->delete('facilities/' . $imageFile);
            }
            DB::commit();
            $facilityList = Facility::orderBy('id', 'desc')->paginate(20);
            return response()->json([
                'status' => true,
                'message' => 'Facility deleted successfully',
                'html' => view(
                    'backend.pages.facilities.partials.facilities-list',
                    compact('facilityList')
                )->render(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Facility deletion error: ' . $e->getMessage(), [
                'exception' => $e,
                'facility_id' => $id
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Delete failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
