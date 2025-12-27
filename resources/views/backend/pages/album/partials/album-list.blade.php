<table class="table card-table table-vcenter text-nowrap mb-0">
    <thead class="thead-light">
        <tr>
            <th>Album Name</th>
            <th>Status</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>
    </thead>

    <tbody>
        @if($albums->count() > 0)
        @foreach($albums as $album)
        <tr>
            <td>
                {{ $album->title }}
            </td>

            <td>
                @if($album->status)
                <span class="badge bg-success">Active</span>
                @else
                <span class="badge bg-danger">Inactive</span>
                @endif
            </td>

            <td>
                {{ $album->created_at->format('d M Y') }}
            </td>

            <td class="action-table-data">
                <div class="edit-delete-action">
                    <a class="btn btn-sm btn-primary me-2 p-2"
                        href="javascript:;"
                        data-title="Edit Album"
                        data-size="lg"
                        data-albumid="{{ $album->id }}"
                        data-ajax-edit-album="true"
                        data-action="normal_album" 
                        data-url="{{ route('manage-album.edit', $album->id) }}"
                        title="Edit">
                        <i data-feather="edit" class="feather-edit"></i>
                    </a>
                    <form action="{{ route('manage-album.destroy', $album->id) }}"
                        method="POST"
                        class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="btn btn-sm btn-danger show_confirm_delete_album"
                            data-name="Album {{ $album->title }}"
                            title="Delete">
                            <i data-feather="trash-2" class="feather-trash-2"></i>
                        </button>
                    </form>

                </div>
            </td>
        </tr>
        @endforeach
        @else
        <tr>
            <td colspan="4" class="text-center text-muted">
                No albums found.
            </td>
        </tr>
        @endif
    </tbody>
</table>