<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\Video;
use Cloudinary\Cloudinary;

class VideoController extends Controller
{
    public function index(){ 
        $videoList = Video::orderBy('id', 'desc')->paginate(20); 
        //return response()->json($testimonialsList);
        return view('backend.pages.video.index', compact('videoList'));
    }

    public function create(Request $request){
        $form = '
        <div class="modal-body">
            <form method="POST" action="' . route('manage-video.store') . '" accept-charset="UTF-8" enctype="multipart/form-data" id="videoAddForm">
                ' . csrf_field() . '
                <div class="row">                    
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="video_file" class="form-label">Select Video File</label>
                            <input type="file" id="video_file" name="video_file" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="progress" style="height: 25px; display:none;" id="uploadProgressWrapper">
                            <div id="uploadProgress" class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                                role="progressbar" style="width:0%">0%</div>
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
            $validator = Validator::make($request->all(), [
                'video_file' => 'required|file|mimes:mp4,mov,avi,mkv,webm|max:204800',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Please fix the following errors',
                    'errors' => $validator->errors()
                ], 422);
            }
            DB::beginTransaction();
            try {
                $file = $request->file('video_file');
                if (!$file->isValid()) {
                    throw new \Exception('Invalid file uploaded.');
                }
                $safeTitle = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
                $uniqueTimestamp = round(microtime(true) * 1000);
                $extension = $file->getClientOriginalExtension();
                $fileName = 'video_'. $uniqueTimestamp . '.' . $extension;
                $cloudName = config('cloudinary.cloud_name');
                $apiKey = config('cloudinary.api_key');
                $apiSecret = config('cloudinary.api_secret');            
                if (empty($cloudName) || empty($apiKey) || empty($apiSecret)) {
                    throw new \Exception('Cloudinary configuration is missing. Please check your environment variables.');
                }
                $cloudinary = new Cloudinary([
                    'cloud' => [
                        'cloud_name' => $cloudName,
                        'api_key'    => $apiKey,
                        'api_secret' => $apiSecret,
                    ],
                    'url' => ['secure' => true],
                ]);
                $result = $cloudinary->uploadApi()->upload(
                    $file->getRealPath(),
                    [
                        'folder' => 'videos',
                        'resource_type' => 'video',
                        'public_id' => $fileName,
                        'display_name' => $safeTitle,
                        'timeout' => 300
                    ]
                );
                $videoUrl = $result['secure_url'] ?? null;
                if (!$videoUrl) {
                    throw new \Exception('Cloudinary upload failed - no secure URL returned.');
                }
                $video = Video::create([
                    'file' => $videoUrl,
                    'original_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'status' => 1
                ]);
                DB::commit();            
                $videoList = Video::orderBy('id', 'desc')->paginate(20);            
                return response()->json([
                    'status' => 'success',
                    'message' => 'Video uploaded successfully!',
                    'videoListData' => view('backend.pages.video.partials.video-list', compact('videoList'))->render()
                ]);

            } catch (\Exception $e) {
                DB::rollBack();            
                $errorMessage = 'Failed to upload video';
                if (str_contains($e->getMessage(), 'Cloudinary configuration')) {
                    $errorMessage = 'Server configuration error. Please contact administrator.';
                } elseif (str_contains($e->getMessage(), 'Invalid file')) {
                    $errorMessage = 'The uploaded file is invalid or corrupted.';
                } elseif (str_contains($e->getMessage(), 'timeout')) {
                    $errorMessage = 'Upload timed out. Please try again with a smaller file or check your internet connection.';
                } elseif (str_contains($e->getMessage(), 'Cloudinary upload failed')) {
                    $errorMessage = 'Failed to upload to cloud storage. Please try again.';
                } else {
                    $errorMessage = $e->getMessage();
                }
                Log::error('Video upload failed: ' . $e->getMessage());
                Log::error('File details: ', [
                    'name' => $file->getClientOriginalName() ?? 'unknown',
                    'size' => $file->getSize() ?? 0,
                    'type' => $file->getMimeType() ?? 'unknown'
                ]);
                return response()->json([
                    'status' => 'error',
                    'message' => $errorMessage
                ], 500);
            }
        }
    
    public function edit($id)
    {
        $video = Video::findOrFail($id);
        $form = '
        <div class="modal-body">
            <form method="POST" action="' . route('manage-video.update', $video->id) . '" 
                accept-charset="UTF-8" enctype="multipart/form-data" id="videoEditForm">
                ' . csrf_field() . method_field('PUT') . '
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="video_file" class="form-label">Replace Video File (optional)</label>
                        <input type="file" id="video_file" name="video_file" class="form-control">
                        <small class="text-muted">Leave empty if you don\'t want to change the video.</small>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select id="status" name="status" class="form-control form-select">
                            <option value="1" ' . ($video->status == 1 ? 'selected' : '') . '>Active</option>
                            <option value="0" ' . ($video->status == 0 ? 'selected' : '') . '>Inactive</option>
                        </select>
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
            'message' => 'Form loaded successfully',
            'form' => $form,
        ]);
    }

    public function update(Request $request, $id)
    {
        Log::info('Update method called for video ID: ' . $id);
        Log::info('Request data:', $request->all());        
        $video = Video::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'video_file' => 'nullable|file|mimes:mp4,mov,avi,mkv,webm|max:204800',
            'status'     => 'required|in:0,1',
        ]);        
        if ($validator->fails()) {
            Log::error('Validation failed:', $validator->errors()->toArray());
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        DB::beginTransaction();
        try {
            $cloudinary = new Cloudinary([
                'cloud' => [
                    'cloud_name' => config('cloudinary.cloud_name'),
                    'api_key'    => config('cloudinary.api_key'),
                    'api_secret' => config('cloudinary.api_secret'),
                ],
                'url' => ['secure' => true],
            ]);            
            if ($request->hasFile('video_file')) {
                Log::info('New video file provided');
                if ($video->file) {
                    try {
                        $urlParts = explode('/', $video->file);
                        $publicIdWithExtension = end($urlParts);
                        $publicId = pathinfo($publicIdWithExtension, PATHINFO_FILENAME);
                        $folder = 'videos';                        
                        $fullPublicId = $folder . '/' . $publicId;                        
                        $cloudinary->uploadApi()->destroy($fullPublicId, [
                            'resource_type' => 'video'
                        ]);
                        Log::info('Old Cloudinary video deleted: ' . $fullPublicId);
                    } catch (\Exception $e) {
                        Log::error('Failed to delete old Cloudinary video: ' . $e->getMessage());
                    }
                }                
                $file = $request->file('video_file');
                $safeTitle = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
                $uniqueTimestamp = round(microtime(true) * 1000);
                $extension = $file->getClientOriginalExtension();
                $fileName = 'video_'. $uniqueTimestamp . '.' . $extension;                
                $result = $cloudinary->uploadApi()->upload(
                    $file->getRealPath(),
                    [
                        'folder' => 'videos',
                        'resource_type' => 'video',
                        'public_id' => $fileName,
                        'display_name' => $safeTitle
                    ]
                );                
                $videoUrl = $result['secure_url'] ?? null;
                if (!$videoUrl) {
                    throw new \Exception('Cloudinary upload failed.');
                }
                $video->file = $videoUrl;
                Log::info('New video uploaded to Cloudinary: ' . $videoUrl);
            }
            
            $video->status = $request->status;
            $video->save();            
            DB::commit();
            Log::info('Video updated successfully');            
            $videoList = Video::orderBy('id', 'desc')->paginate(20);            
            return response()->json([
                'status' => 'success',
                'message' => 'Video updated successfully!',
                'videoListData' => view('backend.pages.video.partials.video-list', compact('videoList'))->render()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Update failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $video = Video::findOrFail($id);
            $cloudinary = new Cloudinary([
                'cloud' => [
                    'cloud_name' => config('cloudinary.cloud_name'),
                    'api_key'    => config('cloudinary.api_key'),
                    'api_secret' => config('cloudinary.api_secret'),
                ],
                'url' => ['secure' => true],
            ]);
            if ($video->file) {
                try {
                    $urlParts = explode('/', $video->file);
                    $publicIdWithExtension = end($urlParts);
                    $publicId = pathinfo($publicIdWithExtension, PATHINFO_FILENAME);
                    $folder = 'videos';                    
                    $fullPublicId = $folder . '/' . $publicId;                    
                    $cloudinary->uploadApi()->destroy($fullPublicId, [
                        'resource_type' => 'video'
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to delete Cloudinary video: ' . $e->getMessage());
                }
            }
            $video->delete();            
            DB::commit();
            $videoList = Video::orderBy('id', 'desc')->paginate(20);
            return response()->json([
                'status' => 'success',
                'message' => 'Video deleted successfully!',
                'videoListData' => view('backend.pages.video.partials.video-list', compact('videoList'))->render()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete video: ' . $e->getMessage()
            ], 500);
        }
    }
}
