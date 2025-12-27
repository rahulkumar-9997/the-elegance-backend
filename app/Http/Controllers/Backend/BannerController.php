<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Cloudinary\Cloudinary;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


class BannerController extends Controller
{
	public function index()
	{
		$banners = Banner::orderBy('id', 'desc')->get();
		return view('backend.pages.banner.index', compact('banners'));
	}

	public function create(Request $request)
	{
		$form = '
        <div class="modal-body">
            <form method="POST" action="' . route('manage-banner.store') . '" accept-charset="UTF-8" enctype="multipart/form-data" id="bannerForm">
                ' . csrf_field() . '
                <div class="row">                    
                    <div class="col-sm-6 col-12">
                        <div class="mb-3">
                            <label class="form-label" for="banner_desktop_video">Banner Desktop Video File *</label>
                            <input type="file" class="form-control" name="banner_desktop_video" id="banner_desktop_video">
                        </div>
                    </div>
                    <div class="col-sm-6 col-12">
                        <div class="mb-3">
                            <label class="form-label" for="banner_mobile_video">Banner Mobile Video File</label>
                            <input type="file" class="form-control" name="banner_mobile_video" id="banner_mobile_img">
                        </div>
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
			'banner_desktop_video' => 'required|file|mimes:mp4,mov,avi,mkv,webm|max:204800',
			'banner_mobile_video'  => 'nullable|file|mimes:mp4,mov,avi,mkv,webm|max:204800',
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

			$desktopVideoUrl = null;
			$mobileVideoUrl = null;

			/** ---------- DESKTOP VIDEO ---------- */
			if ($request->hasFile('banner_desktop_video')) {
				$desktopFile = $request->file('banner_desktop_video');

				if (!$desktopFile->isValid()) {
					throw new \Exception('Invalid desktop video file uploaded.');
				}

				$desktopSafeTitle = Str::slug(pathinfo($desktopFile->getClientOriginalName(), PATHINFO_FILENAME));
				$desktopTimestamp = round(microtime(true) * 1000);
				$desktopExtension = $desktopFile->getClientOriginalExtension();
				$desktopFileName = 'banner_desktop_' . $desktopTimestamp . '.' . $desktopExtension;

				$desktopResult = $cloudinary->uploadApi()->upload(
					$desktopFile->getRealPath(),
					[
						'folder' => 'banners/desktop',
						'resource_type' => 'video',
						'public_id' => $desktopFileName,
						'display_name' => $desktopSafeTitle,
						'timeout' => 300
					]
				);

				$desktopVideoUrl = $desktopResult['secure_url'] ?? null;

				if (!$desktopVideoUrl) {
					throw new \Exception('Desktop video upload to Cloudinary failed - no secure URL returned.');
				}
			}

			/** ---------- MOBILE VIDEO ---------- */
			if ($request->hasFile('banner_mobile_video')) {
				$mobileFile = $request->file('banner_mobile_video');

				if (!$mobileFile->isValid()) {
					throw new \Exception('Invalid mobile video file uploaded.');
				}

				$mobileSafeTitle = Str::slug(pathinfo($mobileFile->getClientOriginalName(), PATHINFO_FILENAME));
				$mobileTimestamp = round(microtime(true) * 1000);
				$mobileExtension = $mobileFile->getClientOriginalExtension();
				$mobileFileName = 'banner_mobile_' . $mobileTimestamp . '.' . $mobileExtension;

				$mobileResult = $cloudinary->uploadApi()->upload(
					$mobileFile->getRealPath(),
					[
						'folder' => 'banners/mobile',
						'resource_type' => 'video',
						'public_id' => $mobileFileName,
						'display_name' => $mobileSafeTitle,
						'timeout' => 300
					]
				);

				$mobileVideoUrl = $mobileResult['secure_url'] ?? null;

				if (!$mobileVideoUrl) {
					throw new \Exception('Mobile video upload to Cloudinary failed - no secure URL returned.');
				}
			}

			/** ---------- SAVE TO DATABASE ---------- */
			$banner = Banner::create([
				'desktop_video' => $desktopVideoUrl,
				'mobile_video'  => $mobileVideoUrl,
			]);

			DB::commit();
			$banners = Banner::orderBy('id', 'desc')->paginate(20);
			return response()->json([
				'status' => 'success',
				'message' => 'Banner video(s) uploaded successfully!',
				'videoListData' => view('backend.pages.banner.partials.banner-list', compact('banners'))->render()
			]);
		} catch (\Exception $e) {
			DB::rollBack();
			$errorMessage = 'Failed to upload banner video(s)';
			if (str_contains($e->getMessage(), 'Cloudinary configuration')) {
				$errorMessage = 'Server configuration error. Please contact administrator.';
			} elseif (str_contains($e->getMessage(), 'Invalid file')) {
				$errorMessage = 'The uploaded file is invalid or corrupted.';
			} elseif (str_contains($e->getMessage(), 'Cloudinary upload failed')) {
				$errorMessage = 'Failed to upload to cloud storage. Please try again.';
			} elseif (str_contains($e->getMessage(), 'timeout')) {
				$errorMessage = 'Upload timed out. Please try again with a smaller file or check your internet connection.';
			} else {
				$errorMessage = $e->getMessage();
			}

			Log::error('Banner upload failed: ' . $e->getMessage());

			Log::error('Desktop file details: ', [
				'name' => isset($desktopFile) ? $desktopFile->getClientOriginalName() : 'not uploaded',
				'size' => isset($desktopFile) ? $desktopFile->getSize() : 0,
				'type' => isset($desktopFile) ? $desktopFile->getMimeType() : 'unknown'
			]);

			Log::error('Mobile file details: ', [
				'name' => isset($mobileFile) ? $mobileFile->getClientOriginalName() : 'not uploaded',
				'size' => isset($mobileFile) ? $mobileFile->getSize() : 0,
				'type' => isset($mobileFile) ? $mobileFile->getMimeType() : 'unknown'
			]);

			return response()->json([
				'status' => 'error',
				'message' => $errorMessage
			], 500);
		}
	}


	public function edit(Request $request, $id)
	{
		$banner = Banner::findOrFail($id);
		$form = '
        <div class="modal-body">
            <form method="POST" action="' . route('manage-banner.update', $banner->id) . '"  accept-charset="UTF-8" enctype="multipart/form-data" id="bannerEditForm">
                 '. csrf_field() . method_field('PUT') .'
                <div class="row">                    
                    <div class="col-sm-6 col-12">
                        <div class="mb-3">
                            <label class="form-label" for="banner_desktop_video">Banner Desktop Video File *</label>
                            <input type="file" class="form-control" name="banner_desktop_video" id="banner_desktop_video">
							<small class="text-muted">Leave empty if you don\'t want to change the video.</small>';
							if($banner->desktop_video){
								$form .='
								<video width="100" height="100" controls>
									<source src="'.$banner->desktop_video.'" type="video/mp4">
									Your browser does not support the video tag.
								</video>';
							}
							$form .='
                        </div>
                    </div>
                    <div class="col-sm-6 col-12">
                        <div class="mb-3">
                            <label class="form-label" for="banner_mobile_video">Banner Mobile Video File</label>
                            <input type="file" class="form-control" name="banner_mobile_video" id="banner_mobile_img">
							<small class="text-muted">Leave empty if you don\'t want to change the video.</small>';
							if($banner->mobile_video){
								$form .='
								<video width="100" height="100" controls>
									<source src="'.$banner->mobile_video.'" type="video/mp4">
									Your browser does not support the video tag.
								</video>';
							}
							$form .='
                        </div>
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

	public function update(Request $request, $id)
	{
		$banner = Banner::findOrFail($id);		
		$validator = Validator::make($request->all(), [
			'banner_desktop_video' => 'nullable|file|mimes:mp4,mov,avi,mkv,webm|max:204800',
			'banner_mobile_video'  => 'nullable|file|mimes:mp4,mov,avi,mkv,webm|max:204800',
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

			$desktopVideoUrl = $banner->desktop_video;
			$mobileVideoUrl = $banner->mobile_video;

			/** ---------- UPDATE DESKTOP VIDEO ---------- */
			if ($request->hasFile('banner_desktop_video')) {
				$desktopFile = $request->file('banner_desktop_video');

				if (!$desktopFile->isValid()) {
					throw new \Exception('Invalid desktop video file uploaded.');
				}

				// Delete old desktop video from Cloudinary if exists
				if ($banner->desktop_video) {
					try {
						$oldDesktopPath = parse_url($banner->desktop_video, PHP_URL_PATH);
						$oldDesktopPublicId = pathinfo($oldDesktopPath, PATHINFO_FILENAME);
						$oldDesktopFolder = 'banners/desktop';
						$fullPublicId = $oldDesktopFolder . '/' . $oldDesktopPublicId;
						
						$cloudinary->uploadApi()->destroy($fullPublicId, ['resource_type' => 'video']);
						Log::info('Deleted old desktop video from Cloudinary: ' . $fullPublicId);
					} catch (\Exception $e) {
						Log::warning('Failed to delete old desktop video from Cloudinary: ' . $e->getMessage());
					}
				}

				$desktopSafeTitle = Str::slug(pathinfo($desktopFile->getClientOriginalName(), PATHINFO_FILENAME));
				$desktopTimestamp = round(microtime(true) * 1000);
				$desktopExtension = $desktopFile->getClientOriginalExtension();
				$desktopFileName = 'banner_desktop_' . $desktopTimestamp . '.' . $desktopExtension;

				$desktopResult = $cloudinary->uploadApi()->upload(
					$desktopFile->getRealPath(),
					[
						'folder' => 'banners/desktop',
						'resource_type' => 'video',
						'public_id' => $desktopFileName,
						'display_name' => $desktopSafeTitle,
						'timeout' => 300
					]
				);

				$desktopVideoUrl = $desktopResult['secure_url'] ?? null;
				if (!$desktopVideoUrl) {
					throw new \Exception('Desktop video upload to Cloudinary failed - no secure URL returned.');
				}
			}

			/** ---------- UPDATE MOBILE VIDEO ---------- */
			if ($request->hasFile('banner_mobile_video')) {
				$mobileFile = $request->file('banner_mobile_video');

				if (!$mobileFile->isValid()) {
					throw new \Exception('Invalid mobile video file uploaded.');
				}
				if ($banner->mobile_video) {
					try {
						$oldMobilePath = parse_url($banner->mobile_video, PHP_URL_PATH);
						$oldMobilePublicId = pathinfo($oldMobilePath, PATHINFO_FILENAME);
						$oldMobileFolder = 'banners/mobile';
						$fullPublicId = $oldMobileFolder . '/' . $oldMobilePublicId;
						
						$cloudinary->uploadApi()->destroy($fullPublicId, ['resource_type' => 'video']);
						Log::info('Deleted old mobile video from Cloudinary: ' . $fullPublicId);
					} catch (\Exception $e) {
						Log::warning('Failed to delete old mobile video from Cloudinary: ' . $e->getMessage());
					}
				}

				$mobileSafeTitle = Str::slug(pathinfo($mobileFile->getClientOriginalName(), PATHINFO_FILENAME));
				$mobileTimestamp = round(microtime(true) * 1000);
				$mobileExtension = $mobileFile->getClientOriginalExtension();
				$mobileFileName = 'banner_mobile_' . $mobileTimestamp . '.' . $mobileExtension;

				$mobileResult = $cloudinary->uploadApi()->upload(
					$mobileFile->getRealPath(),
					[
						'folder' => 'banners/mobile',
						'resource_type' => 'video',
						'public_id' => $mobileFileName,
						'display_name' => $mobileSafeTitle,
						'timeout' => 300
					]
				);

				$mobileVideoUrl = $mobileResult['secure_url'] ?? null;

				if (!$mobileVideoUrl) {
					throw new \Exception('Mobile video upload to Cloudinary failed - no secure URL returned.');
				}
			}
			/** ---------- UPDATE DATABASE ---------- */
			$banner->update([
				'desktop_video' => $desktopVideoUrl,
				'mobile_video'  => $mobileVideoUrl,
			]);
			DB::commit();			
			$banners = Banner::orderBy('id', 'desc')->paginate(20);
			return response()->json([
				'status' => 'success',
				'message' => 'Banner video(s) updated successfully!',
				'videoListData' => view('backend.pages.banner.partials.banner-list', compact('banners'))->render()
			]);
			
		} catch (\Exception $e) {
			DB::rollBack();			
			$errorMessage = 'Failed to update banner video(s)';
			if (str_contains($e->getMessage(), 'Cloudinary configuration')) {
				$errorMessage = 'Server configuration error. Please contact administrator.';
			} elseif (str_contains($e->getMessage(), 'Invalid file')) {
				$errorMessage = 'The uploaded file is invalid or corrupted.';
			} elseif (str_contains($e->getMessage(), 'Cloudinary upload failed')) {
				$errorMessage = 'Failed to upload to cloud storage. Please try again.';
			} elseif (str_contains($e->getMessage(), 'timeout')) {
				$errorMessage = 'Upload timed out. Please try again with a smaller file or check your internet connection.';
			} else {
				$errorMessage = $e->getMessage();
			}
			Log::error('Banner update failed: ' . $e->getMessage());
			return response()->json([
				'status' => 'error',
				'message' => $errorMessage
			], 500);
		}
	}

	public function destroy($id)
	{
		DB::beginTransaction();
		try {
			$banner = Banner::findOrFail($id);			
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
			/** ---------- DELETE DESKTOP VIDEO FROM CLOUDINARY ---------- */
			if ($banner->desktop_video) {
				try {
					$desktopPath = parse_url($banner->desktop_video, PHP_URL_PATH);
					$desktopPublicId = pathinfo($desktopPath, PATHINFO_FILENAME);
					$desktopFolder = 'banners/desktop';
					$fullDesktopPublicId = $desktopFolder . '/' . $desktopPublicId;
					
					$cloudinary->uploadApi()->destroy($fullDesktopPublicId, ['resource_type' => 'video']);
					Log::info('Deleted desktop video from Cloudinary: ' . $fullDesktopPublicId);
				} catch (\Exception $e) {
					Log::warning('Failed to delete desktop video from Cloudinary: ' . $e->getMessage());
				}
			}

			/** ---------- DELETE MOBILE VIDEO FROM CLOUDINARY ---------- */
			if ($banner->mobile_video) {
				try {
					$mobilePath = parse_url($banner->mobile_video, PHP_URL_PATH);
					$mobilePublicId = pathinfo($mobilePath, PATHINFO_FILENAME);
					$mobileFolder = 'banners/mobile';
					$fullMobilePublicId = $mobileFolder . '/' . $mobilePublicId;
					
					$cloudinary->uploadApi()->destroy($fullMobilePublicId, ['resource_type' => 'video']);
					Log::info('Deleted mobile video from Cloudinary: ' . $fullMobilePublicId);
				} catch (\Exception $e) {
					Log::warning('Failed to delete mobile video from Cloudinary: ' . $e->getMessage());
				}
			}
			/** ---------- DELETE FROM DATABASE ---------- */
			$banner->delete();
			DB::commit();			
			$banners = Banner::orderBy('id', 'desc')->paginate(20);			
			return response()->json([
				'status' => 'success',
				'message' => 'Banner deleted successfully!',
				'videoListData' => view('backend.pages.banner.partials.banner-list', compact('banners'))->render()
			]);			
		} catch (\Exception $e) {
			DB::rollBack();			
			$errorMessage = 'Failed to delete banner';			
			if (str_contains($e->getMessage(), 'findOrFail')) {
				$errorMessage = 'Banner not found. It may have already been deleted.';
			} elseif (str_contains($e->getMessage(), 'Cloudinary configuration')) {
				$errorMessage = 'Server configuration error. Please contact administrator.';
			} else {
				$errorMessage = $e->getMessage();
			}			
			Log::error('Banner deletion failed: ' . $e->getMessage());			
			return response()->json([
				'status' => 'error',
				'message' => $errorMessage
			], 500);
		}
	}
}
