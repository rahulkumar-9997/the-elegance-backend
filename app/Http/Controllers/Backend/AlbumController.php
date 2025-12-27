<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Album;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AlbumController extends Controller
{
    public function index()
    {
        $albums = Album::orderBy('id', 'desc')->get();
        return view('backend.pages.album.index', compact('albums'));
    }

    public function create(Request $request)
    {
        $data_action = $request->input('data_action');
        $form = '
        <div class="modal-body">
            <form method="POST" action="' . route('manage-album.store') . '" accept-charset="UTF-8" enctype="multipart/form-data" id="albumAddForm">
                ' . csrf_field() . '
                    <input type="hidden" name="data_action" value="' . $data_action . '">
                    <div class="row">                    
                        <div class="col-sm-12 col-12">
                            <div class="mb-3">
                                <label class="form-label" for="album_name">Album Name *</label>
                                <input type="text" class="form-control" name="album_name" id="album_name">
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
            'album_name' => 'required|string|max:255|unique:albums,title',
        ], [
            'album_name.unique' => 'This album name already exists.'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        DB::beginTransaction();
        try {
            $album_create = Album::create([
                'title'  => $request->album_name,
                'status' => $request->has('status') ? 1 : 0,
            ]);
            DB::commit();
            $albums = Album::latest()->get();
            $html = view('backend.pages.album.partials.album-list', compact('albums'))->render();

            return response()->json([
                'status'  => true,
                'message' => 'Album created successfully',
                'html'    => $html,
                'albumAction'  => $request->data_action,
                'album' => [
                    'id' => $album_create->id,
                    'title' => $album_create->title
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }

    public function edit(Request $request, $id)
    {
        $album = Album::findOrFail($id);
        $data_action = $request->input('data_action');
        $checked = $album->status ? 'checked' : '';
        $form = '
        <div class="modal-body">
            <form method="POST"
                action="' . route('manage-album.update', $album->id) . '"
                id="albumEditForm">
                ' . csrf_field() . '
                ' . method_field('PUT') . '
                <input type="hidden" name="data_action" value="' . $data_action . '">
                <input type="hidden" name="album_id" value="' . $album->id . '">
                <div class="row">                    
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="form-label">Album Name *</label>
                            <input type="text"
                                class="form-control"
                                name="album_name"
                                id="album_name"
                                value="' . $album->title . '">
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input"
                                type="checkbox"
                                id="status"
                                name="status"
                                ' . $checked . '>
                            <label class="form-check-label" for="status">Status</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer pb-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>';

        return response()->json([
            'status' => true,
            'form'   => $form,
        ]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'album_name' => 'required|string|max:255|unique:albums,title,' . $id,
        ], [
            'album_name.unique' => 'This album name already exists.'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        DB::beginTransaction();
        try {
            $album = Album::findOrFail($id);
            $album->update([
                'title'  => $request->album_name,
                'status' => $request->has('status') ? 1 : 0,
            ]);
            DB::commit();
            $albums = Album::latest()->get();
            $html = view('backend.pages.album.partials.album-list', compact('albums'))->render();
            return response()->json([
                'status'      => true,
                'message'     => 'Album updated successfully',
                'html'        => $html,
                'albumAction' => $request->data_action
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $album = Album::with('galleries')->findOrFail($id);
            // foreach ($album->galleries as $gallery) {
            //     if ($gallery->gallery_image && Storage::disk('public')->exists($gallery->gallery_image)) {
            //         Storage::disk('public')->delete($gallery->gallery_image);
            //     }
            // }
            $album->delete();
            DB::commit();
            $albums = Album::latest()->get();
            $html = view('backend.pages.album.partials.album-list', compact('albums'))->render();
            return response()->json([
                'status'  => true,
                'message' => 'Album and its gallery deleted successfully',
                'html'    => $html,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => false,
                'message' => 'Failed to delete album and gallery'
            ], 500);
        }
    }


}
