<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Testimonial;

class TestimonialsController extends Controller
{
    public function index()
    {
        $testimonialList = Testimonial::orderBy('id', 'desc')->paginate(20);
        //dd($testimonialList);
        return view('backend.pages.testimonials.index', compact('testimonialList'));
    }

    public function create(Request $request)
    {
        $form = '
        <div class="modal-body">
            <form method="POST" action="' . route('manage-testimonials.store') . '" id="testimonialsAddForm">
                ' . csrf_field() . '
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" placeholder="Excellent" id="title">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Guest Type</label>
                            <input type="text" name="guest_type" class="form-control" placeholder="Guests" id="guest_type">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label">Date Visited</label>
                            <input type="text" name="visit_date" class="form-control datepicker" placeholder="Select visit date" id="visit_date"
                            autocomplete="off" id="basic-datepicker">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label">Review</label>
                            <textarea name="review_text" rows="4" class="form-control" id="review_text"></textarea>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Value</label>
                        <input type="number" step="0.1" max="5" min="0" name="value_rating" class="form-control" id="value_rating">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Rooms</label>
                        <input type="number" step="0.1" max="5" min="0" name="rooms_rating" class="form-control" id="rooms_rating">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Location</label>
                        <input type="number" step="0.1" max="5" min="0" name="location_rating" class="form-control" id="location_rating">
                    </div>
                    <div class="col-md-4 mt-2">
                        <label class="form-label">Cleanliness</label>
                        <input type="number" step="0.1" max="5" min="0" name="cleanliness_rating" class="form-control" id="cleanliness_rating">
                    </div>
                    <div class="col-md-4 mt-2">
                        <label class="form-label">Service</label>
                        <input type="number" step="0.1" max="5" min="0" name="service_rating" class="form-control" id="service_rating">
                    </div>
                    <div class="col-md-4 mt-2">
                        <label class="form-label">Sleep Quality</label>
                        <input type="number" step="0.1" max="5" min="0" name="sleep_quality_rating" class="form-control" id="sleep_quality_rating">
                    </div>
                    <div class="col-md-12 mt-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="status" value="1" checked id="status">
                            <label class="form-check-label">Active</label>
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
        $request->merge([
            'status' => $request->has('status') ? 1 : 0
        ]);
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'guest_type' => 'nullable|string|max:100',
            'visit_date' => 'nullable|date',
            'review_text' => 'nullable|string',
            'value_rating' => 'nullable|numeric|min:0|max:5',
            'rooms_rating' => 'nullable|numeric|min:0|max:5',
            'location_rating' => 'nullable|numeric|min:0|max:5',
            'cleanliness_rating' => 'nullable|numeric|min:0|max:5',
            'service_rating' => 'nullable|numeric|min:0|max:5',
            'sleep_quality_rating' => 'nullable|numeric|min:0|max:5',
            'status' => 'required|in:0,1',
        ], [
            'title.required' => 'Title is required',
            'title.string' => 'Title must be a valid string',
            'title.max' => 'Title must not exceed 255 characters',
            'guest_type.string' => 'Guest type must be a valid string',
            'guest_type.max' => 'Guest type must not exceed 100 characters',
            'visit_date.date' => 'Please enter a valid date',
            'review_text.string' => 'Review must be a valid text',
            'value_rating.numeric' => 'Value rating must be a number',
            'value_rating.min' => 'Value rating must be at least 0',
            'value_rating.max' => 'Value rating must not exceed 5',
            'rooms_rating.numeric' => 'Rooms rating must be a number',
            'rooms_rating.min' => 'Rooms rating must be at least 0',
            'rooms_rating.max' => 'Rooms rating must not exceed 5',
            'location_rating.numeric' => 'Location rating must be a number',
            'location_rating.min' => 'Location rating must be at least 0',
            'location_rating.max' => 'Location rating must not exceed 5',
            'cleanliness_rating.numeric' => 'Cleanliness rating must be a number',
            'cleanliness_rating.min' => 'Cleanliness rating must be at least 0',
            'cleanliness_rating.max' => 'Cleanliness rating must not exceed 5',
            'service_rating.numeric' => 'Service rating must be a number',
            'service_rating.min' => 'Service rating must be at least 0',
            'service_rating.max' => 'Service rating must not exceed 5',
            'sleep_quality_rating.numeric' => 'Sleep quality rating must be a number',
            'sleep_quality_rating.min' => 'Sleep quality rating must be at least 0',
            'sleep_quality_rating.max' => 'Sleep quality rating must not exceed 5',
            'status.required' => 'Status is required',
            'status.in' => 'Status must be either active or inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }
        DB::beginTransaction();
        try {
            $testimonial = Testimonial::create([
                'title' => $request->title,
                'guest_type' => $request->guest_type,
                'visit_date' => $request->visit_date,
                'review_text' => $request->review_text,
                'value_rating' => $request->value_rating,
                'rooms_rating' => $request->rooms_rating,
                'location_rating' => $request->location_rating,
                'cleanliness_rating' => $request->cleanliness_rating,
                'service_rating' => $request->service_rating,
                'sleep_quality_rating' => $request->sleep_quality_rating,
                'status' => $request->status,
            ]);
            DB::commit();
            $testimonialList = Testimonial::orderBy('id', 'desc')->paginate(20);
            return response()->json([
                'status' => true,
                'message' => 'Testimonial added successfully',
                'html' => view(
                    'backend.pages.testimonials.partials.testimonials-list',
                    compact('testimonialList')
                )->render(),
                'testimonial_id' => $testimonial->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Testimonial store error: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->all()
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Failed to add testimonial. Please try again.',
                'debug' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
    public function edit($id)
    {
        $testimonial = Testimonial::findOrFail($id);
        $form = '
        <div class="modal-body">
            <form method="POST" action="' . route('manage-testimonials.update', $testimonial->id) . '" id="testimonialsEditForm">
                ' . csrf_field() . '
                ' . method_field('PUT') . '
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" value="' . htmlspecialchars($testimonial->title) . '" id="title">
                            <div class="invalid-feedback" id="title_error"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Guest Type</label>
                            <input type="text" name="guest_type" class="form-control" value="' . htmlspecialchars($testimonial->guest_type) . '" id="guest_type">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label">Date Visited</label>
                            <input type="text" name="visit_date" class="form-control datepicker" 
                                value="' . ($testimonial->visit_date ? date('Y-m-d', strtotime($testimonial->visit_date)) : '') . '"
                                placeholder="Select visit date" autocomplete="off" id="basic-datepicker-edit" id="visit_date">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label">Review</label>
                            <textarea name="review_text" rows="4" class="form-control" id="review_text">' . htmlspecialchars($testimonial->review_text) . '</textarea>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Value</label>
                        <input type="number" step="0.1" max="5" min="0" name="value_rating" class="form-control" value="' . $testimonial->value_rating . '" id="value_rating">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Rooms</label>
                        <input type="number" step="0.1" max="5" min="0" name="rooms_rating" class="form-control" value="' . $testimonial->rooms_rating . '" id="rooms_rating">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Location</label>
                        <input type="number" step="0.1" max="5" min="0" name="location_rating" class="form-control" value="' . $testimonial->location_rating . '" id="location_rating">
                    </div>
                    <div class="col-md-4 mt-2">
                        <label class="form-label">Cleanliness</label>
                        <input type="number" step="0.1" max="5" min="0" name="cleanliness_rating" class="form-control" value="' . $testimonial->cleanliness_rating . '" id="cleanliness_rating">
                    </div>
                    <div class="col-md-4 mt-2">
                        <label class="form-label">Service</label>
                        <input type="number" step="0.1" max="5" min="0" name="service_rating" class="form-control" value="' . $testimonial->service_rating . '" id="service_rating">
                    </div>
                    <div class="col-md-4 mt-2">
                        <label class="form-label">Sleep Quality</label>
                        <input type="number" step="0.1" max="5" min="0" name="sleep_quality_rating" class="form-control" value="' . $testimonial->sleep_quality_rating . '" id="sleep_quality_rating">
                    </div>
                    <div class="col-md-12 mt-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="status" value="1" ' . ($testimonial->status == 1 ? 'checked' : '') . ' id="status">
                            <label class="form-check-label">Active</label>
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
        $testimonial = Testimonial::findOrFail($id);
        $request->merge([
            'status' => $request->has('status') ? 1 : 0
        ]);
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'guest_type' => 'nullable|string|max:100',
            'visit_date' => 'nullable|date',
            'review_text' => 'nullable|string',
            'value_rating' => 'nullable|numeric|min:0|max:5',
            'rooms_rating' => 'nullable|numeric|min:0|max:5',
            'location_rating' => 'nullable|numeric|min:0|max:5',
            'cleanliness_rating' => 'nullable|numeric|min:0|max:5',
            'service_rating' => 'nullable|numeric|min:0|max:5',
            'sleep_quality_rating' => 'nullable|numeric|min:0|max:5',
            'status' => 'required|in:0,1',
        ], [
            'title.required' => 'Title is required',
            'title.string' => 'Title must be a valid string',
            'title.max' => 'Title must not exceed 255 characters',
            'guest_type.string' => 'Guest type must be a valid string',
            'guest_type.max' => 'Guest type must not exceed 100 characters',
            'visit_date.date' => 'Please enter a valid date',
            'review_text.string' => 'Review must be a valid text',
            'value_rating.numeric' => 'Value rating must be a number',
            'value_rating.min' => 'Value rating must be at least 0',
            'value_rating.max' => 'Value rating must not exceed 5',
            'rooms_rating.numeric' => 'Rooms rating must be a number',
            'rooms_rating.min' => 'Rooms rating must be at least 0',
            'rooms_rating.max' => 'Rooms rating must not exceed 5',
            'location_rating.numeric' => 'Location rating must be a number',
            'location_rating.min' => 'Location rating must be at least 0',
            'location_rating.max' => 'Location rating must not exceed 5',
            'cleanliness_rating.numeric' => 'Cleanliness rating must be a number',
            'cleanliness_rating.min' => 'Cleanliness rating must be at least 0',
            'cleanliness_rating.max' => 'Cleanliness rating must not exceed 5',
            'service_rating.numeric' => 'Service rating must be a number',
            'service_rating.min' => 'Service rating must be at least 0',
            'service_rating.max' => 'Service rating must not exceed 5',
            'sleep_quality_rating.numeric' => 'Sleep quality rating must be a number',
            'sleep_quality_rating.min' => 'Sleep quality rating must be at least 0',
            'sleep_quality_rating.max' => 'Sleep quality rating must not exceed 5',
            'status.required' => 'Status is required',
            'status.in' => 'Status must be either active or inactive',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }
        DB::beginTransaction();
        try {
            $testimonial->update([
                'title' => $request->title,
                'guest_type' => $request->guest_type,
                'visit_date' => $request->visit_date,
                'review_text' => $request->review_text,
                'value_rating' => $request->value_rating,
                'rooms_rating' => $request->rooms_rating,
                'location_rating' => $request->location_rating,
                'cleanliness_rating' => $request->cleanliness_rating,
                'service_rating' => $request->service_rating,
                'sleep_quality_rating' => $request->sleep_quality_rating,
                'status' => $request->status,
            ]);
            DB::commit();
            $testimonialList = Testimonial::orderBy('id', 'desc')->paginate(20);
            return response()->json([
                'status' => true,
                'message' => 'Testimonial updated successfully',
                'html' => view(
                    'backend.pages.testimonials.partials.testimonials-list',
                    compact('testimonialList')
                )->render(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Testimonial update error: ' . $e->getMessage(), [
                'exception' => $e,
                'testimonial_id' => $id,
                'request_data' => $request->all()
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Failed to update testimonial. Please try again.',
                'debug' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $testimonial = Testimonial::findOrFail($id);
            $testimonial->delete();
            DB::commit();
            $testimonialList = Testimonial::orderBy('id', 'desc')->paginate(20);
            return response()->json([
                'status' => true,
                'message' => 'Testimonial deleted successfully',
                'html' => view(
                    'backend.pages.testimonials.partials.testimonials-list',
                    compact('testimonialList')
                )->render(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Testimonial deletion error: ' . $e->getMessage(), [
                'exception' => $e,
                'testimonial_id' => $id
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete testimonial: ' . $e->getMessage(),
            ], 500);
        }
    }
}
