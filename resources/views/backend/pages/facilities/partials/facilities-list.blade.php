@if(isset($facilityList) && $facilityList->count() > 0)
<table class="table datatable1">
    <thead class="thead-light">
        <tr>
            <th width="60">#</th>
            <th>Title</th>
            <th width="150">Image</th>
            <th>Description</th>
            <th width="120">Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($facilityList as $key => $facility)
        <tr>
            <td class="text-center">{{ ($facilityList->currentPage() - 1) * $facilityList->perPage() + $loop->iteration }}</td>
            <td>
                <div class="d-flex align-items-center">
                    <div>
                        <h6 class="mb-0">{{ $facility->title }}</h6>
                    </div>
                </div>
            </td>
            <td class="align-middle">
                @if($facility->image)
                <img src="{{ asset('storage/facilities/' . $facility->image) }}"
                    class="img-thumbnail"
                    width="60"
                    height="60"
                    alt="{{ $facility->title }}"
                    style="object-fit: cover;">
                @else
                <span class="text-muted">No image</span>
                @endif
            </td>
            <td>
                @if($facility->short_desc)
                <small class="text-muted">{{ Str::limit($facility->short_desc, 80) }}</small>
                @else
                <span class="text-muted">-</span>
                @endif
            </td>
            <td class="action-table-data align-middle">
                <div class="edit-delete-action">
                    <!-- Edit Button -->
                    <a class="btn btn-sm btn-primary me-2 p-2"
                        href="javascript:;"
                        data-title="Edit Facility - {{ $facility->title }}"
                        data-size="md"
                        data-ajax-edit-facility="true"
                        data-url="{{ route('manage-facilities.edit', $facility->id) }}"
                        title="Edit">
                        <i class="ti ti-edit"></i>
                    </a>

                    <!-- Delete Button -->
                    <form action="{{ route('manage-facilities.destroy', $facility->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="btn btn-sm btn-danger show_confirm_delete_facility"
                            data-name="{{ $facility->title }}"
                            title="Delete">
                            <i class="ti ti-trash"></i>
                        </button>
                    </form>
                </div>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

@if(isset($facilityList) && $facilityList->hasPages())
<div class="my-pagination mt-3 mb-3" id="facility-list-pagination">
    {{ $facilityList->links('vendor.pagination.bootstrap-5') }}
</div>
@endif

@else
<div class="text-center py-5">
    <div class="alert alert-info">
        <i class="ti ti-building me-2"></i>
        <h5 class="mt-2 mb-3">No Facilities Found</h5>
        <p class="mb-3">Start by adding your first facility to showcase hotel amenities.</p>
        <a href="javascript:void(0)"
            data-ajax-facilities-add-popup="true"
            data-size="md"
            data-title="Add New Facility"
            data-url="{{ route('manage-facilities.create') }}"
            class="btn btn-primary">
            <i class="ti ti-circle-plus me-1"></i>
            Add First Facility
        </a>
    </div>
</div>
@endif