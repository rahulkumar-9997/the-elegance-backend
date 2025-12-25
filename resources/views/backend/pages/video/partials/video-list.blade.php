<table class="table datatable1">
    <thead class="thead-light">
        <tr>
            <th>Video</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @if(isset($videoList) && $videoList->count() > 0)
        @foreach($videoList as $video)
        <tr>
            <td>
                {{-- show video player if file exists --}}
                @if($video->file)
                <video width="200" height="200" controls>
                    <source src="{{ $video->file }}" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
                @else
                <span class="text-muted">No video</span>
                @endif
            </td>
            <td>
                @if($video->status == 1)
                <span class="badge bg-success">Active</span>
                @else
                <span class="badge bg-info">Inactive</span>
                @endif
            </td>

            <td class="action-table-data">
                <div class="edit-delete-action">
                    {{-- Edit Button --}}
                    <a class="btn btn-sm btn-primary me-2 p-2"
                        href="javascript:;"
                        data-title="Edit Video"
                        data-size="lg"
                        data-videoid="{{ $video->id }}"
                        data-ajax-edit-video="true"
                        data-url="{{ route('manage-video.edit', $video->id) }}"
                        title="Edit">
                        <i data-feather="edit" class="feather-edit"></i>
                    </a>
                    <form action="{{ route('manage-video.destroy', $video->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger show_confirm"
                            data-name="Video ID {{ $video->id }}" title="Delete">
                            <i data-feather="trash-2" class="feather-trash-2"></i>
                        </button>
                    </form>
                </div>
            </td>
        </tr>
        @endforeach
        @else
        <tr>
            <td colspan="3" class="text-center">No videos found.</td>
        </tr>
        @endif
    </tbody>
</table>
<div class="my-pagination mt-3 mb-3" id="video-list-pagination">
    {{ $videoList->links('vendor.pagination.bootstrap-4') }}
</div>