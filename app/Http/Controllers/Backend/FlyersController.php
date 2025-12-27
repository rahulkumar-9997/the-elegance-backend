<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
            <form method="POST" action="' . route('manage-gallery.store') . '" accept-charset="UTF-8" enctype="multipart/form-data" id="galleryAddForm">
                ' . csrf_field() . '
                <div class="row">                    
                                      
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
}
