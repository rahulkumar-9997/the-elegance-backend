@if(isset($testimonialList) && $testimonialList->count() > 0)
<table class="table datatable1">
    <thead class="thead-light">
        <tr>
            <th width="50">#</th>
            <th>Title</th>
            <th>Guest Type</th>
            <th>Visit Date</th>
            <th>Ratings</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($testimonialList as $key => $testimonial)
        <tr>
            <td class="text-center">{{ ($testimonialList->currentPage() - 1) * $testimonialList->perPage() + $loop->iteration }}</td>
            <td>
                <div class="d-flex align-items-center">
                    <div>
                        <h6 class="mb-0">{{ $testimonial->title }}</h6>
                        @if($testimonial->review_text)
                        <small class="text-muted text-truncate d-block" style="max-width: 300px;"
                            title="{{ $testimonial->review_text }}">
                            {{ Str::limit($testimonial->review_text, 60) }}
                        </small>
                        @endif
                    </div>
                </div>
            </td>
            <td>
                @if($testimonial->guest_type)
                <span class="badge bg-info">{{ $testimonial->guest_type }}</span>
                @else
                <span class="text-muted">-</span>
                @endif
            </td>
            <td>
                @if($testimonial->visit_date)
                <span class="text-muted">{{ $testimonial->visit_date->format('d M, Y') }}</span>
                @else
                <span class="text-muted">-</span>
                @endif
            </td>
            <td>
                @php
                $ratings = [
                'value' => $testimonial->value_rating,
                'rooms' => $testimonial->rooms_rating,
                'location' => $testimonial->location_rating,
                'cleanliness' => $testimonial->cleanliness_rating,
                'service' => $testimonial->service_rating,
                'sleep' => $testimonial->sleep_quality_rating,
                ];

                $nonZeroRatings = array_filter($ratings, function($rating) {
                return !is_null($rating) && $rating > 0;
                });

                $averageRating = count($nonZeroRatings) > 0
                ? array_sum($nonZeroRatings) / count($nonZeroRatings)
                : 0;
                @endphp

                @if($averageRating > 0)
                <div class="d-flex align-items-center">
                    <div class="rating-stars me-2">
                        @for($i = 1; $i <= 5; $i++)
                            @if($i <=round($averageRating))
                                <i class="ti ti-star-filled text-warning"></i>
                            @else
                                <i class="ti ti-star text-muted"></i>
                            @endif
                        @endfor
                    </div>
                    <small class="text-muted">{{ number_format($averageRating, 1) }}/5</small>
                </div>
                @else
                <span class="text-muted">No ratings</span>
                @endif
            </td>
            <td>
                @if($testimonial->status == 1)
                <span class="badge bg-success">Active</span>
                @else
                <span class="badge bg-secondary">Inactive</span>
                @endif
            </td>
            <td>
                <div class="action-table-data">
                    <div class="edit-delete-action">                        
                        <a class="btn btn-sm btn-primary me-2 p-2"
                            href="javascript:;"
                            data-title="Edit Testimonial - {{ $testimonial->title }}"
                            data-size="lg"
                            data-ajax-edit-testimonial="true"
                            data-url="{{ route('manage-testimonials.edit', $testimonial->id) }}"
                            title="Edit">
                            <i class="ti ti-edit"></i>
                        </a>
                        <form action="{{ route('manage-testimonials.destroy', $testimonial->id) }}"
                            method="POST"
                            class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="btn btn-sm btn-danger show_confirm_delete_testimonial"
                                data-name="{{ $testimonial->title }}"
                                title="Delete">
                                <i class="ti ti-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

@if(isset($testimonialList) && $testimonialList->hasPages())
<div class="my-pagination mt-3 mb-3" id="testimonial-list-pagination">
    {{ $testimonialList->links('vendor.pagination.bootstrap-5') }}
</div>
@endif

@else
<div class="text-center py-5">
    <div class="alert alert-info">
        <i class="ti ti-message-circle me-2"></i>
        <h5 class="mt-2 mb-3">No Testimonials Found</h5>
        <p class="mb-3">Start by adding your first testimonial to showcase guest feedback.</p>
        <a href="javascript:void(0)"
            data-ajax-testimonials-add-popup="true"
            data-size="lg"
            data-title="Add New Testimonial"
            data-url="{{ route('manage-testimonials.create') }}"
            class="btn btn-primary">
            <i class="ti ti-circle-plus me-1"></i>
            Add First Testimonial
        </a>
    </div>
</div>
@endif