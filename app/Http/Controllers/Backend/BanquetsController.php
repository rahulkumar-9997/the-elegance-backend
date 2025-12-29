<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\Banquet;
use App\Models\BanquetImage;
use App\Helpers\ImageHelper;

class BanquetsController extends Controller
{
    public function index()
    {
        $banquetList = Banquet::with('images')->paginate(20);
        return view('backend.pages.banquets.index', compact('banquetList'));
    }

     public function create(Request $request){
        $banquets = Banquet::orderBy('id', 'desc')->get();
        $form = '
        <div class="modal-body">
            <form method="POST" action="' . route('manage-banquets.store') . '" accept-charset="UTF-8" enctype="multipart/form-data" id="banquetsAddForm">
                ' . csrf_field() . '
                <div class="row">                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">
                                Select Banquets
                                <span class="text-danger ms-1">*</span>
                            </label>
                            <select class="select form-select" name="banquets_id" id="banquets_id">
                                <option value="">-- Select Banquets --</option>';
                                foreach($banquets as $banquet){
                                    $form .='
                                    <option value="'.$banquet->id.'">
                                        '.$banquet->title.'
                                    </option>';
                                }
                                $form .='
                            </select>
                        </div>
                    </div> 
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="banquets_image" class="form-label">Select Banquets Image *</label>
                            <input type="file" id="banquets_image" name="banquets_image[]" class="form-control" multiple>
                        </div>
                    </div>
                    <!--<div class="col-sm-12 col-12">
                        <div class="mb-3 col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="status" name="status" checked>
                                <label class="form-check-label" for="status">Status</label>
                            </div>
                        </div>
                    </div> -->              
                                
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
                        'order' => ++$maxOrder
                    ]);
                }
            } else {
                throw new \Exception('No image files provided.');
            }
            
            DB::commit();
            $banquetList = Banquet::with('images')->paginate(20);
            return response()->json([
                'status' => true,
                'message' => 'Banquet images added successfully',
                'html' => view('backend.pages.banquets.partials.banquets-list', 
                    compact('banquetList'))->render(),
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
}
