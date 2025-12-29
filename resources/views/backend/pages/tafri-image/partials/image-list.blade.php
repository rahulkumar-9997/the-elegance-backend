<table class="table datatable1">
    <thead class="thead-light">
        <tr>
            <th width="60">Order</th>
            <th width="80">Image</th>
            <th>Title</th>
            <th width="100">Actions</th>
        </tr>
    </thead>
    <tbody>
        @if(isset($tafriImageList) && $tafriImageList->count() > 0)
        @foreach($tafriImageList as $image)
        <tr>
            <td class="align-middle">
                <div class="d-flex gap-1" style="min-width: 80px;">
                    @if(!$loop->first)
                    <form action="{{ route('manage-tafri-lounge-image.order-up', $image->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-secondary px-2" title="Move Up">
                            <i class="fa fa-chevron-up"></i>
                        </button>
                    </form>
                    @endif
                    @if(!$loop->last)
                    <form action="{{ route('manage-tafri-lounge-image.order-down', $image->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-secondary px-2" title="Move Down">
                            <i class="fa fa-chevron-down"></i>
                        </button>
                    </form>
                    @endif
                </div>
            </td>
            <td class="align-middle">
                @if($image->image_file)
                <img class="img-thumbnail"
                    src="{{ asset('storage/tafri/' . $image->image_file) }}"
                    width="60"
                    height="60"
                    alt="{{ $image->title }}"
                    style="object-fit: cover;">
                @else
                <span class="text-muted">No image</span>
                @endif
            </td>
            <td class="align-middle">
                {{ $image->title }}
            </td>
            <td class="action-table-data align-middle">
                <div class="edit-delete-action">
                    <a class="btn btn-sm btn-primary me-2 p-2"
                        href="javascript:;"
                        data-title="Edit Tafri Image"
                        data-size="md"
                        data-ajax-edit-tafri-image="true"
                        data-url="{{ route('manage-tafri-lounge-image.edit', $image->id) }}"
                        title="Edit">
                        <i class="fa fa-edit"></i>
                    </a>
                    <form action="{{ route('manage-tafri-lounge-image.destroy', $image->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="btn btn-sm btn-danger show_confirm_tafri_image_delete"
                            data-name="{{ $image->title }}"
                            title="Delete">
                            <i class="fa fa-trash"></i>
                        </button>
                    </form>
                </div>
            </td>
        </tr>
        @endforeach
        @else
        <tr>
            <td colspan="4" class="text-center">No tafri images found.</td>
        </tr>
        @endif
    </tbody>
</table>

@if(isset($tafriImageList) && $tafriImageList->hasPages())
<div class="my-pagination mt-3 mb-3" id="tafri-image-list-pagination">
    {{ $tafriImageList->links('vendor.pagination.bootstrap-5') }}
</div>
@endif