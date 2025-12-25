<table class="table datatable1">
    <thead class="thead-light">
        <tr>
            <th>Title</th>
            <th>Image</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @if(isset($galleryList) && $galleryList->count() > 0)
            @foreach($galleryList as $gallery)
                <tr>
                    <td>{{ $gallery->title ?? '-' }}</td>
                    <td>
                        @if($gallery->image)
                            <img src="{{ asset('upload/gallery/' . $gallery->image) }}" width="100" alt="{{ $gallery->title }}">
                        @else
                            -
                        @endif
                    </td>
                   
                    <td>
                        @if($gallery->status == 1)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-info">Inactive</span>
                        @endif
                    </td>
                    <td class="action-table-data">
                        <div class="edit-delete-action">
                            <a class="btn btn-sm btn-primary me-2 p-2"
                                href="javascript:;" 
                                data-title="Edit Gallery Item"
                                data-size="lg"
                                data-galleryid="{{ $gallery->id }}"
                                data-ajax-edit-gallery="true"
                                data-url="{{ route('manage-gallery.edit', $gallery->id) }}"
                                title="Edit">
                                <i data-feather="edit" class="feather-edit"></i>
                            </a>
                            <form action="{{ route('manage-gallery.destroy', $gallery->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger show_confirm" data-name="{{ $gallery->title }}" title="Delete">
                                    <i data-feather="trash-2" class="feather-trash-2"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        @else
            <tr>
                <td colspan="5" class="text-center">No gallery items found.</td>
            </tr>
        @endif
    </tbody>
</table>
<div class="my-pagination mt-3 mb-3" id="blog-list-pagination">
    {{ $galleryList->links('vendor.pagination.bootstrap-4') }}
</div> 