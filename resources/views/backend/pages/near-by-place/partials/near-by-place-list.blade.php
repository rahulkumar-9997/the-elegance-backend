<table class="table datatable1">
    <thead class="thead-light">
        <tr>
            <th width="5%">#</th>
            <th>Title</th>
            <th>Slug</th>
            <th>Image</th>
            <th width="10%">Status</th>
            <th width="15%">Actions</th>
        </tr>
    </thead>
    <tbody>
        @if(isset($nearByPlaceList) && $nearByPlaceList->count() > 0)
        @foreach($nearByPlaceList as $index => $place)
        <tr>
            <td>{{ $nearByPlaceList->firstItem() + $index }}</td>
            <td>
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="mb-0">{{ $place->title ?? '-' }}</h6>
                        @if($place->short_desc)
                        <small class="text-muted">{{ Str::limit($place->short_desc, 50) }}</small>
                        @endif
                    </div>
                </div>
            </td>
            <td>
                <code class="bg-light p-1 rounded">{{ $place->slug ?? '-' }}</code>
            </td>
            <td>
                @if($place->image)
                <img class="img-thumbnail"
                    src="{{ asset('storage/nearby-places/' . $place->image) }}"
                    width="60"
                    height="60"
                    alt="{{ $place->title }}"
                    style="object-fit: cover;">
                @else
                <span class="badge bg-secondary">No Image</span>
                @endif
            </td>

            <td>
                @if($place->status == 1)
                <span class="badge bg-success">Active</span>
                @else
                <span class="badge bg-secondary">Inactive</span>
                @endif
            </td>
            <td class="action-table-data">
                <div class="edit-delete-action d-flex gap-2">
                    <a class="btn btn-sm btn-primary me-2 p-2"
                        href="{{ route('manage-near-by-place.edit', $place->id) }}"
                        title="Edit" data-title="Edit" data-bs-toggle="tooltip">
                        <i data-feather="edit" class="feather-16"></i>
                    </a>
                    <form action="{{ route('manage-near-by-place.destroy', $place->id) }}"
                        method="POST"
                        class="delete-form d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="button"
                            class="btn btn-sm btn-danger delete-btn"
                            data-id="{{ $place->id }}"
                            data-title="{{ $place->title }}"
                            title="Delete" data-bs-toggle="tooltip">
                            <i data-feather="trash-2" class="feather-16"></i>
                        </button>
                    </form>
                    @if($place->attractions_status == 0)
                    <form action="{{ route('manage-near-by-place.add-to-attractions', $place->id) }}"
                        method="POST"
                        class="d-inline add-to-attraction-form">
                        @csrf
                        <button type="button"
                            class="btn btn-sm btn-info p-2 show_confirm_add_attraction"
                            data-name="{{ $place->title }}"
                            data-action="add"
                            title="Add to Attractions" data-bs-toggle="tooltip">
                            <i class="fas fa-star"></i>
                        </button>
                    </form>
                    @else
                    <!-- Remove from Attraction Button -->
                    <form action="{{ route('manage-near-by-place.remove-from-attractions', $place->id) }}"
                        method="POST"
                        class="d-inline remove-from-attraction-form">
                        @csrf
                        @method('PUT')
                        <button type="button"
                            class="btn btn-sm btn-warning p-2 show_confirm_remove_attraction"
                            data-name="{{ $place->title }}"
                            data-action="remove"
                            title="Remove from Attractions" data-bs-toggle="tooltip">
                            <i class="fas fa-star-half-alt"></i>
                        </button>
                    </form>
                    @endif

                    <div class="btn-group ms-2 d-flex gap-1" role="group">
                        @if(!$loop->first)
                        <form action="{{ route('manage-near-by-place.order-up', $place->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-secondary" title="Move Up" data-bs-toggle="tooltip">
                                <i data-feather="chevron-up" class="feather-16"></i>
                            </button>
                        </form>
                        @endif
                        @if(!$loop->last)
                        <form action="{{ route('manage-near-by-place.order-down', $place->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-secondary" title="Move Down" data-bs-toggle="tooltip">
                                <i data-feather="chevron-down" class="feather-16"></i>
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </td>
        </tr>
        @endforeach
        @else
        <tr>
            <td colspan="6" class="text-center py-4">
                <div class="text-muted">
                    <i data-feather="map-pin" class="feather-64 mb-3"></i>
                    <h5>No Near By Places Found</h5>
                    <p>Click the "Add New" button to create your first near by place.</p>
                </div>
            </td>
        </tr>
        @endif
    </tbody>
</table>

@if(isset($nearByPlaceList) && $nearByPlaceList->hasPages())
<div class="my-pagination mt-3 mb-3" id="place-list-pagination">
    {{ $nearByPlaceList->links('vendor.pagination.bootstrap-5') }}
</div>
@endif