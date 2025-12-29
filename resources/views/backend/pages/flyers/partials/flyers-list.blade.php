<table class="table datatable1">
    <thead class="thead-light">
        <tr>
            <th>Link</th>
            <th>Image</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @if(isset($flyersList) && $flyersList->count() > 0)
        @foreach($flyersList as $key => $flyer)
        <tr>
           
            <td class="align-middle">
                @if($flyer->flyers_link)
                <a href="{{ $flyer->flyers_link }}" target="_blank" class="text-truncate" style="max-width: 200px; display: inline-block;" title="{{ $flyer->flyers_link }}">
                    {{ Str::limit($flyer->flyers_link, 30) }}
                </a>
                @else
                -
                @endif
            </td>
            <td class="align-middle">
                @if($flyer->image_file)
                <img class="img-thumbnail"
                    src="{{ asset('storage/flyers/' . $flyer->image_file) }}"
                    width="60"
                    height="60"
                    alt="Flyer Image"
                    style="object-fit: cover;">
                @else
                <span class="text-muted">No image</span>
                @endif
            </td>
            <td class="align-middle">
                @if($flyer->status == 1)
                <span class="badge bg-success">Active</span>
                @else
                <span class="badge bg-secondary">Inactive</span>
                @endif
            </td>
            <td class="action-table-data">
                <div class="d-flex gap-1">
                    <a class="btn btn-sm btn-primary me-2 p-2"
                        href="javascript:;"
                        data-title="Edit Flyer"
                        data-size="lg"
                        data-flyerid="{{ $flyer->id }}"
                        data-ajax-edit-flyer="true"
                        data-url="{{ route('manage-flyers.edit', $flyer->id) }}"
                        title="Edit" data-bs-toggle="tooltip">
                        <i data-feather="edit" class="feather-edit"></i>
                    </a>
                    <form action="{{ route('manage-flyers.destroy', $flyer->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger show_confirm_delete_flyers"
                            title="Delete"  data-bs-toggle="tooltip">
                            <i data-feather="trash-2" class="feather-trash-2"></i>
                        </button>
                    </form>
                    <div class="btn-group d-flex gap-1" role="group">
                        @if(!$loop->first)
                        <form action="{{ route('manage-flyers.order-up', $flyer->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-secondary" title="Move Up" data-bs-toggle="tooltip">
                                <i data-feather="chevron-up" class="feather-16"></i>
                            </button>
                        </form>
                        @endif
                        @if(!$loop->last)
                        <form action="{{ route('manage-flyers.order-down', $flyer->id) }}" method="POST" class="d-inline">
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
            <td colspan="5" class="text-center">No flyers found.</td>
        </tr>
        @endif
    </tbody>
</table>

@if(isset($flyersList) && $flyersList->hasPages())
<div class="my-pagination mt-3 mb-3" id="flyer-list-pagination">
    {{ $flyersList->links('vendor.pagination.bootstrap-5') }}
</div>
@endif