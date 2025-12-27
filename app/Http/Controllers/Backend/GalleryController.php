<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Album;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Helpers\ImageHelper;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Gallery;
class GalleryController extends Controller
{
    public function index(){ 
        $galleryList = Gallery::with('album')->orderBy('id', 'desc')->paginate(20); 
        return view('backend.pages.gallery.index', compact('galleryList'));
    }

    public function create(Request $request){
        $albums = Album::orderBy('id', 'desc')->get();
        $form = '
        <div class="modal-body">
            <form method="POST" action="' . route('manage-gallery.store') . '" accept-charset="UTF-8" enctype="multipart/form-data" id="galleryAddForm">
                ' . csrf_field() . '
                <div class="row">                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <div class="add-newplus">
                                <label class="form-label">
                                    Select Album
                                    <span class="text-danger ms-1">*</span>
                                </label>
                                <a href="javascript:void(0);"
                                    data-title="Add New Album"
                                    data-ajax-album-add="true"
                                    data-url="' . route('manage-album.create') . '"
                                    data-size="lg"
                                    data-action="dropdown">
                                    <i data-feather="plus-circle" class="plus-down-add"></i>
                                    <span>Add New Album</span>
                                </a>
                            </div>
                            <select class="select form-select" name="select_album" id="select_album">
                                <option value="">-- Select Album --</option>';
                                foreach($albums as $album){
                                    $form .='
                                    <option value="'.$album->id.'">
                                        '.$album->title.'
                                    </option>';
                                }
                                $form .='
                            </select>
                        </div>
                    </div>                   
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="gallery" class="form-label">Select Multiple Image File (Minimum 20 files required)</label>
                            <input type="file" id="gallery" name="gallery[]" multiple class="form-control">
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
            'select_album' => 'required|exists:albums,id',
            'gallery'      => 'required|array|min:1',
            'gallery.*'    => 'image|mimes:jpeg,png,jpg,gif,webp|max:6144',
        ], [
            'select_album.required' => 'Please select album',
            'gallery.min'           => 'Please upload at least 1 image',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $uploadedImages = [];
            foreach ($request->file('gallery') as $file) {
                $safeTitle = Str::slug(
                    pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)
                );
                $fileName = 'gallery_' . uniqid() . '.webp';
                $path = storage_path('app/public/gallery/' . $fileName);
                if (!file_exists(dirname($path))) {
                    mkdir(dirname($path), 0755, true);
                }
                ImageHelper::saveAsWebp($file, $path, 75);
                $gallery = Gallery::create([
                    'album_id'      => $request->select_album,
                    'gallery_image' => $fileName,
                    'title'         => $safeTitle,
                ]);
                $uploadedImages[] = $gallery;
            }
            DB::commit();
            $galleryList = Gallery::with('album')
                ->latest()
                ->paginate(20);
            return response()->json([
                'status' => 'success',
                'message' => count($uploadedImages) . ' images uploaded successfully',
                'galleryListData' => view('backend.pages.gallery.partials.gallery-list',
                    compact('galleryList')
                )->render(),
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            // Cleanup
            foreach ($uploadedImages ?? [] as $img) {
                Storage::disk('public')->delete('gallery/' . $img->gallery_image);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit(Request $request, $id)
    {
        $gallery = Gallery::with('album')->findOrFail($id);
        $albums = Album::orderBy('id', 'desc')->get();
        $form = '
        <div class="modal-body">
            <form method="POST" action="' . route('manage-gallery.update', $gallery->id) . '" accept-charset="UTF-8" enctype="multipart/form-data" id="galleryEditForm">
                ' . csrf_field() . '
                ' . method_field('PUT') . '
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Select Album <span class="text-danger">*</span></label>
                            <select class="select form-select" name="select_album" id="select_album">
                                <option value="">-- Select Album --</option>';
                                foreach($albums as $album){
                                    $selected = $album->id == $gallery->album_id ? 'selected' : '';
                                    $form .= '<option value="'.$album->id.'" '.$selected.'>'.$album->title.'</option>';
                                }
                        $form .= '</select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Image Title</label>
                            <input type="text" id="title" name="title" class="form-control"> 
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label">Replace Image (Optional)</label>
                            <input type="file" id="gallery" name="gallery" class="form-control">
                            <div class="form-text">Upload only if you want to replace the existing image</div>
                            <div class="mt-2">
                                <img src="'.asset('storage/gallery/'.$gallery->gallery_image).'" width="120" alt="'.$gallery->title.'">
                            </div>
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
            'message' => 'Gallery edit form loaded',
            'form' => $form,
        ]);
    }

    public function update(Request $request, $id)
    {
        $gallery = Gallery::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'select_album' => 'required|exists:albums,id',
            'title'        => 'nullable|string|max:255',
            'gallery'      => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:6144',
        ], [
            'select_album.required' => 'Please select album',
            'gallery.image'         => 'The uploaded file must be an image',
            'gallery.max'           => 'The image size must be less than 6MB',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $gallery->album_id = $request->select_album;
            if ($request->hasFile('gallery')) {
                $file = $request->file('gallery');
                $safeTitle = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
                $fileName = 'gallery_' . uniqid() . '.webp';
                $path = storage_path('app/public/gallery/' . $fileName);
                if (!file_exists(dirname($path))) {
                    mkdir(dirname($path), 0755, true);
                }
                ImageHelper::saveAsWebp($file, $path, 75);
                Storage::disk('public')->delete('gallery/' . $gallery->gallery_image);
                $gallery->gallery_image = $fileName;
                $gallery->title = $request->title ?: $safeTitle;
            }else {
                if ($request->title) {
                    $gallery->title = $request->title;
                }
            }
            $gallery->save();
            DB::commit();
            $galleryList = Gallery::with('album')->latest()->paginate(20);
            return response()->json([
                'status' => 'success',
                'message' => 'Gallery updated successfully',
                'galleryListData' => view('backend.pages.gallery.partials.gallery-list', compact('galleryList'))->render(),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
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
            $gallery = Gallery::findOrFail($id);
            $imagePath = storage_path('app/public/gallery/' . $gallery->gallery_image);
            if (File::exists($imagePath)) {
                File::delete($imagePath);
            }
            $gallery->delete();
            DB::commit();
            $galleryList = Gallery::with('album')->latest()->paginate(20);
            return response()->json([
                'status' => 'success',
                'message' => 'Gallery item deleted successfully!',
                'galleryListData' => view('backend.pages.gallery.partials.gallery-list', compact('galleryList'))->render()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete gallery item: ' . $e->getMessage()
            ], 500);
        }
    }

}
