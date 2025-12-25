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
        @if(isset($awardsList) && $awardsList->count() > 0)
        @foreach($awardsList as $award)
        <tr>
            <td style="width: 400px">
                @if($award->description)
                    {{ Str::limit($award->description, 1000) }}                
                @endif
            </td>
            <td>
                @if($award->image)
                <img src="{{ asset('upload/awards/' . $award->image) }}" width="100" alt="Award Image">
                @else
                -
                @endif
            </td>

            <td>
                @if($award->status == 1)
                <span class="badge bg-success">Active</span>
                @else
                <span class="badge bg-info">Inactive</span>
                @endif
            </td>
            <td class="action-table-data">
                <div class="edit-delete-action">
                    <a class="btn btn-sm btn-primary me-2 p-2"
                        href="javascript:;"
                        data-title="Edit Awards Item"
                        data-size="lg"
                        data-awardsid="{{ $award->id }}"
                        data-ajax-edit-awards="true"
                        data-url="{{ route('manage-awards.edit', $award->id) }}"
                        title="Edit">
                        <i data-feather="edit" class="feather-edit"></i>
                    </a>
                    <form action="{{ route('manage-awards.destroy', $award->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger show_confirm" data-name="{{ $award->title }}" title="Delete">
                            <i data-feather="trash-2" class="feather-trash-2"></i>
                        </button>
                    </form>
                </div>
            </td>
        </tr>
        @endforeach
        @else
        <tr>
            <td colspan="5" class="text-center">No awards items found.</td>
        </tr>
        @endif
    </tbody>
</table>
<div class="my-pagination mt-3 mb-3" id="blog-list-pagination">
    {{ $awardsList->links('vendor.pagination.bootstrap-4') }}
</div>