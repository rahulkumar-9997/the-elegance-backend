<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Helpers\ImageHelper;
use App\Models\NearByPlace;

class NearByPlaceController extends Controller
{
    public function index()
    {
        $nearByPlaceList = NearByPlace::orderBy('order')->paginate(20);
        return view('backend.pages.near-by-place.index', compact('nearByPlaceList'));
    }
    public function create()
    {
        return view('backend.pages.near-by-place.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255|unique:near_by_places,title',
            'short_desc' => 'required|string|max:500',
            'long_description' => 'nullable|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'status' => 'nullable|boolean',
        ], [
            'title.required' => 'The title field is required.',
            'title.unique' => 'This title already exists. Please choose a different title.',
            'short_desc.required' => 'The short content field is required.',
            'image.required' => 'Please upload an image.',
            'image.image' => 'The file must be an image.',
            'image.max' => 'The image size must not exceed 2MB.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('error', 'Please fix the errors below.');
        }

        DB::beginTransaction();
        try {
            $imageName = null;
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $titleSlug = Str::slug($request->title);
                $imageName =  $titleSlug . '-' . uniqid() . '.webp';
                $imagePath = storage_path('app/public/nearby-places/' . $imageName);
                if (!File::exists(dirname($imagePath))) {
                    File::makeDirectory(dirname($imagePath), 0755, true);
                }
                ImageHelper::saveAsWebp($file, $imagePath, 75);
            }
            $nearByPlace = NearByPlace::create([
                'title' => $request->title,
                'short_desc' => $request->short_desc,
                'long_description' => $request->long_description,
                'meta_title' => $request->meta_title,
                'meta_description' => $request->meta_description,
                'image' => $imageName,
                'status' => $request->has('status') ? 1 : 0,
                'order' => NearByPlace::max('order') + 1,
            ]);
            DB::commit();
            return redirect()->route('manage-near-by-place.index')->with('success', 'Near by place added successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($imageName) && File::exists(storage_path('app/public/nearby-places/' . $imageName))) {
                File::delete(storage_path('app/public/nearby-places/' . $imageName));
            }
            return redirect()->back()->withInput()->with('error', 'Failed to add near by place. Error: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $place = NearByPlace::findOrFail($id);
        return view('backend.pages.near-by-place.edit', compact('place'));
    }

    public function update(Request $request, $id)
    {
        $place = NearByPlace::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'title' => [
                'required',
                'string',
                'max:255',
            ],
            'short_desc' => 'nullable|string|max:500',
            'long_description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'status' => 'nullable|boolean',
        ], [
            'title.unique' => 'This title already exists. Please choose a different title.',
            'long_description.required' => 'The content field is required.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('error', 'Please fix the errors below.');
        }

        DB::beginTransaction();
        try {
            $data = [
                'title' => $request->title,
                'short_desc' => $request->short_desc,
                'long_description' => $request->long_description,
                'meta_title' => $request->meta_title,
                'meta_description' => $request->meta_description,
                'status' => $request->has('status') ? 1 : 0,
            ];
            if ($request->hasFile('image')) {
                if ($place->image && File::exists(storage_path('app/public/nearby-places/' . $place->image))) {
                    File::delete(storage_path('app/public/nearby-places/' . $place->image));
                }
                $file = $request->file('image');
                $titleSlug = Str::slug($request->title);
                $imageName = 'nearby_' .$titleSlug. '_' . uniqid() . '.webp';
                $imagePath = storage_path('app/public/nearby-places/' . $imageName);
                ImageHelper::saveAsWebp($file, $imagePath, 75);
                $data['image'] = $imageName;
            }
            $place->update($data);
            DB::commit();
            return redirect()->route('manage-near-by-place.index')->with('success', 'Near by place "' . $request->title . '" updated successfully!')
            ->with('slug_info', 'URL Slug: ' . $place->fresh()->slug);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Failed to update near by place: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $place = NearByPlace::findOrFail($id);        
        DB::beginTransaction();
        try {
            if ($place->image && File::exists(storage_path('app/public/nearby-places/' . $place->image))) {
                File::delete(storage_path('app/public/nearby-places/' . $place->image));
            }            
            $title = $place->title;
            $place->delete();            
            DB::commit();            
            return redirect()->route('manage-near-by-place.index')->with('success', 'Near by place "'.$title.'" deleted successfully!');                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('manage-near-by-place.index')->with('error', 'Failed to delete near by place.');
        }
    }

    public function orderUp($id)
    {
        $place = NearByPlace::findOrFail($id);
        $previousPlace = NearByPlace::where('order', '<', $place->order)->orderBy('order', 'desc')->first();
        if ($previousPlace) {
            $tempOrder = $place->order;
            $place->order = $previousPlace->order;
            $previousPlace->order = $tempOrder;
            $place->save();
            $previousPlace->save();
        }
        return redirect()->back()->with('success', 'Order updated successfully!');
    }

    public function orderDown($id)
    {
        $place = NearByPlace::findOrFail($id);
        $nextPlace = NearByPlace::where('order', '>', $place->order)
            ->orderBy('order', 'asc')
            ->first();
        if ($nextPlace) {
            $tempOrder = $place->order;
            $place->order = $nextPlace->order;
            $nextPlace->order = $tempOrder;
            $place->save();
            $nextPlace->save();
        }
        return redirect()->back()->with('success', 'Order updated successfully!');
    }

    public function addToAttractions($id)
    {
        $place = NearByPlace::findOrFail($id);        
        DB::beginTransaction();
        try {
            $place->update([
                'attractions_status' => 1
            ]);            
            DB::commit();            
            return redirect()->route('manage-near-by-place.index')
            ->with('success', '"' . $place->title . '" has been added to attractions!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('manage-near-by-place.index')
            ->with('error', 'Failed to add to attractions: ' . $e->getMessage());
        }
    }
    
    public function removeFromAttractions($id)
    {
        $place = NearByPlace::findOrFail($id);        
        DB::beginTransaction();
        try {
            $place->update([
                'attractions_status' => 0
            ]);
            DB::commit();            
            return redirect()->route('manage-near-by-place.index')
            ->with('success', '"' . $place->title . '" has been removed from attractions!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('manage-near-by-place.index')
            ->with('error', 'Failed to remove from attractions: ' . $e->getMessage());
        }
    }
}
