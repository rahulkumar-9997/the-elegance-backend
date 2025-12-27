<table class="table card-table table-vcenter text-nowrap mb-0">
    <thead class="thead-light">
        <tr>
            <th>Desktop Video</th>
            <th>Mobile Video</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @if($banners->count() > 0)
        @foreach($banners as $banner)
        <tr>
            <td>
                @if($banner->desktop_video)
                <video width="200" height="200" controls>
                    <source src="{{ $banner->desktop_video }}" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
                @else
                <span class="text-muted">No video</span>
                @endif
               
            </td>
            <td>
                @if($banner->mobile_video)
                <video width="200" height="200" controls>
                    <source src="{{ $banner->mobile_video }}" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
                @else
                <span class="text-muted">No video</span>
                @endif
                
            </td>
            <td class="action-table-data">
                <div class="edit-delete-action">
                    <a class="btn btn-sm btn-primary me-2 p-2"
                        href="javascript:;"
                        data-title="Edit Video"
                        data-size="lg"
                        data-videoid="{{ $banner->id }}"
                        data-ajax-edit-video="true"
                        data-url="{{ route('manage-banner.edit', $banner->id) }}"
                        title="Edit">
                        <i data-feather="edit" class="feather-edit"></i>
                    </a>
                    <form action="{{ route('manage-banner.destroy', $banner->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger show_confirm"
                            data-name="Video ID {{ $banner->id }}" title="Delete">
                            <i data-feather="trash-2" class="feather-trash-2"></i>
                        </button>
                    </form>
                </div>
            </td>
        </tr>
        @endforeach
        @else
        <tr>
            <td colspan="6" class="text-center">No banners found.</td>
        </tr>
        @endif
    </tbody>
</table>