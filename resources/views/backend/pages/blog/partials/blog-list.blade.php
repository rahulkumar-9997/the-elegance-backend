@if($blogs->count() > 0)
    <table class="table">
        <thead class="thead-light">
            <tr>
                <th>Title</th>
                <th>Subtitle</th>
                <th>Status</th>
                <th>Image</th>
                <th>Paragraphs</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($blogs as $blog)
            <tr>
                <td>{{ $blog->title }}</td>
                <td>{{ $blog->short_desc }}</td>
                <td>
                    @if($blog->status === 'published')
                    <span class="badge bg-success">Published</span>
                    @elseif($blog->status === 'draft')
                    <span class="badge bg-warning">Draft</span>
                    @else
                    <span class="badge bg-danger">Archived</span>
                    @endif
                </td>
                <td>
                    @if($blog->featured_image)
                        <img src="{{ asset('upload/blog/' . $blog->featured_image) }}" alt="Banner Image" width="100">
                    @endif
                </td>
                <td>
                    @if($blog->paragraphs->count() > 0)
                        <span class="badge bg-info">{{ $blog->paragraphs->count() }} Paragraphs</span>
                    @else
                        <span class="badge bg-secondary">No Paragraphs</span>   
                    @endif
                </td>
                
                <td class="action-table-data">
                    <div class="edit-delete-action">
                        <a class="btn btn-sm btn-primary me-2 p-2" href="{{ route('manage-blog.edit', $blog->id) }}">
                            <i data-feather="edit" class="feather-edit"></i>
                        </a>
                        <form action="{{ route('manage-blog.destroy', $blog->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger show_confirm" data-name="Delete Blog">
                                <i data-feather="trash-2" class="feather-trash-2"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach 
        </tbody>
    </table>
<div class="my-pagination mt-3 mb-3" id="blog-list-pagination">
    {{ $blogs->links('vendor.pagination.bootstrap-4') }}
</div>  
@endif
