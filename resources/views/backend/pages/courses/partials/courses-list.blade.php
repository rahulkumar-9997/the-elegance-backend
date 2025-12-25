@if($courses->count() > 0)
    <table class="table">
        <thead class="thead-light">
            <tr>
                <th>Title</th>
                <th>Short Description</th>
                <th>Status</th>
                <th>Main Image</th>
                <th>Additional Content</th>
                <th>Highlights</th>
                <th>Course PDF</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($courses as $course)
            <tr>
                <td>{{ $course->title }}</td>
                <td>{!! Str::limit($course->description, 50) !!}</td>
                <td>
                    @if($course->status)
                    <span class="badge bg-success">Published</span>
                    @else
                    <span class="badge bg-warning">Draft</span>
                    @endif
                </td>
                <td>
                    @if($course->main_image)
                        <img src="{{ asset('upload/courses/' . $course->main_image) }}" alt="Course Image" width="100">
                    @endif
                </td>
                <td>
                    @if($course->additionalContents->count() > 0)
                        <span class="badge bg-info">{{ $course->additionalContents->count() }} Additional</span>
                    @else
                        <span class="badge bg-secondary">No Additional</span>   
                    @endif
                </td>
                <td>
                    @if($course->highlightsContents->count() > 0)
                        <span class="badge bg-success">{{ $course->highlightsContents->count() }} Highlights</span>
                    @else
                        <span class="badge bg-secondary">No Highlights</span>   
                    @endif
                </td>
                <td>
                    @if($course->course_pdf_file)
                        <a target="_blank" href="{{ asset('upload/courses/' . $course->course_pdf_file) }}">
                            <span class="badge bg-purple">View Course File</span>
                        <a>
                    @endif
                </td>
                
                <td class="action-table-data">
                    <div class="edit-delete-action">
                        <a class="btn btn-sm btn-primary me-2 p-2" href="{{ route('manage-courses.edit', $course->id) }}">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('manage-courses.destroy', $course->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger show_confirm" data-name="{{ $course->title }}">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach 
        </tbody>
    </table>
<div class="my-pagination mt-3 mb-3" id="courses-list-pagination">
    {{ $courses->links('vendor.pagination.bootstrap-4') }}
</div>  
@else
    <div class="alert alert-info text-center">
        <i class="fas fa-info-circle me-2"></i> No courses found. 
        <a href="{{ route('manage-courses.create') }}" class="alert-link">Create your first course</a>.
    </div>
@endif