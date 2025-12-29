<table class="table datatable1">
    <thead class="thead-light">
        <tr>
            <th>Banquet</th>
            <th>Images</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @if(isset($banquetList) && $banquetList->count() > 0)
        @foreach($banquetList as $banquet)
        <tr>
            <td class="align-middle">
                <div class="d-flex align-items-center">
                    <div>
                        <h6 class="mb-0">{{ $banquet->title }}</h6>
                        <small class="text-muted">Slug: {{ $banquet->slug }}</small><br>
                        <small class="text-muted">Images: {{ $banquet->images->count() }}</small>
                    </div>
                </div>
            </td>
            <td class="align-middle">
                <div class="d-flex flex-wrap gap-2">
                    @if($banquet->images->count() > 0)
                    @foreach($banquet->images->take(4) as $image)
                    <div class="position-relative image-thumbnail-wrapper">
                        <img src="{{ asset('storage/banquets/' . $image->image_file) }}"
                            class="img-thumbnail"
                            width="60"
                            height="60"
                            alt="Banquet Image"
                            style="object-fit: cover;">
                        <form action="{{ route('manage-banquet-images.destroy', $image->id) }}"
                            method="POST"
                            class="position-absolute top-0 end-0">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="btn btn-sm btn-danger btn-icon p-1 show_confirm_image_delete"
                                title="Delete Image"
                                data-name="Image">
                                <i data-feather="x" class="feather-12"></i>
                            </button>
                        </form>
                    </div>
                    @endforeach
                    @if($banquet->images->count() > 4)
                    <div class="image-thumbnail-wrapper">
                        <div class="img-thumbnail bg-light d-flex align-items-center justify-content-center"
                            style="width: 60px; height: 60px;">
                            <span class="text-muted">+{{ $banquet->images->count() - 4 }}</span>
                        </div>
                    </div>
                    @endif
                    @else
                    <span class="text-muted">No images</span>
                    @endif
                </div>
            </td>
            <td class="align-middle">
                <div class="d-flex gap-1">
                    <a class="btn btn-sm btn-success me-2 p-2"
                        href="javascript:;"
                        data-title="Add Images to {{ $banquet->title }}"
                        data-size="md"
                        data-banquetid="{{ $banquet->id }}"
                        data-ajax-add-images="true"
                        data-url="{{ route('manage-banquet-images.create', $banquet->id) }}"
                        title="Add Images">
                        <i data-feather="plus" class="feather-edit"></i> Add Banquets Images
                    </a>
                    <a class="btn btn-sm btn-info me-2 p-2"
                        href="javascript:;"
                        data-title="View All Images - {{ $banquet->title }}"
                        data-size="lg"
                        data-ajax-view-images="true"
                        data-url="{{ route('manage-banquet-images.index', $banquet->id) }}"
                        title="View All Images">
                            <i data-feather="eye" class="feather-edit"></i> View All Images
                    </a>
                </div>
            </td>
        </tr>
        @endforeach
        @else
        <tr>
            <td colspan="3" class="text-center">No banquets found.</td>
        </tr>
        @endif
    </tbody>
</table>

@if(isset($banquetList) && $banquetList->hasPages())
<div class="my-pagination mt-3 mb-3" id="banquet-list-pagination">
    {{ $banquetList->links('vendor.pagination.bootstrap-5') }}
</div>
@endif
