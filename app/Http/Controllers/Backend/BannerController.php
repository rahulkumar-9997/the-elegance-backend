<?php
namespace App\Http\Controllers\Backend;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Banner;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::orderBy('id', 'desc')->get();
        return view('backend.pages.banner.index', compact('banners'));
    }

    public function create()
    {
        return view('backend.pages.banner.create');
    }

    public function store(Request $request)
	{
		$request->validate([
			'banner_heading_name' => 'required|string',
			'banner_link' => 'nullable|url',
			'banner_desktop_img' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
			'banner_mobile_img' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
		]);
		DB::beginTransaction();
		try {
			$destinationPath = public_path('upload/banner');
			if (!File::exists($destinationPath)) {
				File::makeDirectory($destinationPath, 0755, true);
			}

			$desktopImageName = null;
			if ($request->hasFile('banner_desktop_img')) {
				$desktopImage = $request->file('banner_desktop_img');
				$extension = $desktopImage->getClientOriginalExtension();
				$uniqueTimestampDesktop = round(microtime(true) * 1000);
				$desktopImageName = 'desktop-' . $uniqueTimestampDesktop . '.' . $extension;
				$desktopImage->move($destinationPath, $desktopImageName);
			}

			$mobileImageName = null;
			if ($request->hasFile('banner_mobile_img')) {
				$mobileImage = $request->file('banner_mobile_img');
				$extension = $mobileImage->getClientOriginalExtension();
				$uniqueTimestampMobile = round(microtime(true) * 1000);
				$mobileImageName = 'mobile-' . $uniqueTimestampMobile . '.' . $extension;
				$mobileImage->move($destinationPath, $mobileImageName);
			}
			Banner::create([
				'banner_heading_name' => $request->banner_heading_name,
				'banner_link' => $request->banner_link,
				'banner_desktop_img' => $desktopImageName,
				'banner_mobile_img' => $mobileImageName,
			]);

			DB::commit();
			return redirect()->route('manage-banner.index')->with('success', 'Banner created successfully.');
		} catch (\Exception $e) {
			DB::rollBack();
			Log::error('Banner creation failed: ' . $e->getMessage());
			return redirect()->back()->withInput()->with('error', 'Failed to create banner. Please try again.');
		}
	}



    public function edit($id)
    {
        $banner = Banner::findOrFail($id);
        return view('backend.pages.banner.edit', compact('banner'));
    }

   	public function update(Request $request, $id)
	{
		$banner = Banner::findOrFail($id);
		$request->validate([
			'banner_heading_name' => 'nullable|string',
			'banner_link' => 'nullable|url',
			'banner_desktop_img' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
			'banner_mobile_img' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
		]);
		DB::beginTransaction();
		try {
			$data = [
				'banner_heading_name' => $request->banner_heading_name,
				'banner_link' => $request->banner_link,
			];

			$destinationPath = public_path('upload/banner');
			if (!File::exists($destinationPath)) {
				File::makeDirectory($destinationPath, 0755, true);
			}
			if ($request->hasFile('banner_desktop_img')) {
				if ($banner->banner_desktop_img && file_exists($destinationPath . '/' . $banner->banner_desktop_img)) {
					unlink($destinationPath . '/' . $banner->banner_desktop_img);
				}
				$desktopImage = $request->file('banner_desktop_img');
				$extension = $desktopImage->getClientOriginalExtension();
				$uniqueTimestampDesktop = round(microtime(true) * 1000);
				$desktopImageName = 'desktop-' . $uniqueTimestampDesktop . '.' . $extension;
				$desktopImage->move($destinationPath, $desktopImageName);

				$data['banner_desktop_img'] = $desktopImageName;
			}
			if ($request->hasFile('banner_mobile_img')) {
				if ($banner->banner_mobile_img && file_exists($destinationPath . '/' . $banner->banner_mobile_img)) {
					unlink($destinationPath . '/' . $banner->banner_mobile_img);
				}
				$mobileImage = $request->file('banner_mobile_img');
				$extension = $mobileImage->getClientOriginalExtension();
				$uniqueTimestampMobile = round(microtime(true) * 1000);
				$mobileImageName = 'mobile-' . $uniqueTimestampMobile . '.' . $extension;
				$mobileImage->move($destinationPath, $mobileImageName);
				$data['banner_mobile_img'] = $mobileImageName;
			}
			$banner->update($data);
			DB::commit();
			return redirect()->route('manage-banner.index')->with('success', 'Banner updated successfully.');
		} catch (\Exception $e) {
			DB::rollBack();
			Log::error('Banner update failed: ' . $e->getMessage());
			return redirect()->back()->withInput()->with('error', 'Failed to update banner. Please try again.');
		}
	}


    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $banner = Banner::findOrFail($id);
            $destinationPath = public_path('upload/banner');
            if ($banner->banner_desktop_img && File::exists($destinationPath . '/' . $banner->banner_desktop_img)) {
                File::delete($destinationPath . '/' . $banner->banner_desktop_img);
            }
            if ($banner->banner_mobile_img && File::exists($destinationPath . '/' . $banner->banner_mobile_img)) {
                File::delete($destinationPath . '/' . $banner->banner_mobile_img);
            }
            $banner->delete();
            DB::commit();
            return redirect()->route('manage-banner.index')->with('success', 'Banner deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Banner deletion failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete banner. Please try again.');
        }
    }
}
