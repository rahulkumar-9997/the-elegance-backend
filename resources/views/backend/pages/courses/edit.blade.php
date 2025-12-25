@extends('backend.layouts.master')
@section('title','Edit Courses')
@push('styles')
<!-- <link rel="stylesheet" href="{{asset('backend/assets/plugins/summernote/summernote-bs4.min.css')}}"> -->
@endpush
@section('main-content')
<div class="content">
    <div class="page-header">
        <div class="add-item d-flex">
            <div class="page-title">
                <h4 class="fw-bold"></h4>
                <h6>
                    Edit Courses
                </h6>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
            <a href="{{ route('manage-courses.index') }}" data-title="Go Back to Previous Page" data-bs-toggle="tooltip" class="btn btn-sm btn-info" data-bs-original-title="Go Back to Previous Page">
                <i class="ti ti-arrow-left me-1"></i>
                Go Back to Previous Page
            </a>
        </div>
        <div class="card-body">
            @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            <form action="{{ route('manage-courses.update', $course->id) }}" method="POST" enctype="multipart/form-data" id="coursesFormEdit">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-sm-4 col-12">
                        <div class="mb-3">
                            <label class="form-label" for="title">
                                Title <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $course->title) }}" />
                            @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-sm-4 col-12">
                        <div class="mb-3">
                            <label class="form-label" for="short_content">
                                Short Description
                            </label>
                            <textarea class="form-control @error('short_content') is-invalid @enderror" id="short_content" name="short_content" rows="2">{{ old('short_content', $course->short_content) }}</textarea>
                            @error('short_content')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-sm-4 col-12">
                        <div class="mb-3">
                            <label class="form-label" for="main_image">
                                Main Image
                            </label>
                            <input type="file" class="form-control @error('main_image') is-invalid @enderror" name="main_image" id="main_image" />
                            @if($course->main_image)
                            <div class="mt-2">
                                <img src="{{ asset('upload/courses/' . $course->main_image) }}" alt="Current Main Image" width="100" class="img-thumbnail">

                            </div>
                            @endif
                            @error('main_image')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-sm-4 col-12">
                        <div class="mb-3">
                            <label class="form-label" for="page_image">
                                Courses Page Image
                            </label>
                            <input type="file" class="form-control @error('page_image') is-invalid @enderror" name="page_image" id="page_image" />
                            @if($course->page_image)
                            <div class="mt-2">
                                <img src="{{ asset('upload/courses/' . $course->page_image) }}" alt="Current Page Image" width="100" class="img-thumbnail">
                            </div>
                            @endif
                            @error('page_image')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-sm-4 col-12">
                        <div class="mb-3">
                            <label class="form-label" for="course_certificate_title_1">
                                Course Certificate Title 1
                            </label>
                            <input type="text" class="form-control @error('course_certificate_title_1') is-invalid @enderror" name="course_certificate_title_1" id="course_certificate_title_1"
                            value="{{ old('course_certificate_title_1', $course->course_certificate_title_1) }}" />
                            @error('course_certificate_title_1')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-sm-4 col-12">
                        <div class="mb-3">
                            <label class="form-label" for="course_certificate_image_1">
                                Course Certificate Image 1
                            </label>
                            <input type="file" class="form-control @error('course_certificate_image_1') is-invalid @enderror" name="course_certificate_image_1" id="course_certificate_image_1"
                            value="{{ old('course_certificate_image_1') }}" />
                            @if($course->course_certificate)
                            <div class="mt-2">
                                <img src="{{ asset('upload/courses/' . $course->course_certificate) }}" alt="Current Page Image" width="100" class="img-thumbnail">
                            </div>
                            @endif
                            @error('course_certificate_image_1')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-sm-4 col-12">
                        <div class="mb-3">
                            <label class="form-label" for="course_certificate_title_2">
                                Course Certificate Title 2
                            </label>
                            <input type="text" class="form-control @error('course_certificate_title_2') is-invalid @enderror" name="course_certificate_title_2" id="course_certificate_title_2"
                            value="{{ old('course_certificate_title_2', $course->course_certificate_title_2) }}"  />
                            @error('course_certificate_title_2')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-sm-4 col-12">
                        <div class="mb-3">
                            <label class="form-label" for="course_certificate_image_2">
                                Course Certificate Image 2
                            </label>
                            <input type="file" class="form-control @error('course_certificate_image_2') is-invalid @enderror" name="course_certificate_image_2" id="course_certificate_image_2"
                            value="{{ old('course_certificate_image_2') }}" />
                            @if($course->course_certificate_image_2)
                            <div class="mt-2">
                                <img src="{{ asset('upload/courses/' . $course->course_certificate_image_2) }}" alt="Current Page Image" width="100" class="img-thumbnail">
                            </div>
                            @endif
                            @error('course_certificate_image_2')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-sm-4 col-12">
                        <div class="mb-3">
                            <label class="form-label" for="course_pdf_file">
                                Course PDF File (PDF File Only 1-20MB accepted )
                            </label>
                            <input type="file" class="form-control @error('course_pdf_file') is-invalid @enderror" name="course_pdf_file" id="course_pdf_file" 
                            value="{{ old('course_pdf_file') }}"/>
                            @if($course->course_pdf_file)
                            <div class="mt-2">
                                <a target="_blank" href="{{ asset('upload/courses/' . $course->course_pdf_file) }}">
                                    <span class="badge bg-purple">View Course File</span>
                                </a>
                            </div>
                            @endif
                            @error('course_pdf_file')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-sm-4 col-12">
                        <div class="mb-3">
                            <label class="form-label" for="meta_title">Meta title</label>
                            <input type="text" class="form-control @error('meta_title') is-invalid @enderror" name="meta_title" id="meta_title" value="{{ old('meta_title', $course->meta_title) }}" />
                            @error('meta_title')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-sm-8 col-12">
                        <div class="mb-3">
                            <label class="form-label" for="meta_description">
                                Meta Description
                            </label>
                            <textarea class="form-control @error('meta_description') is-invalid @enderror" id="meta_description" name="meta_description" rows="2">{{ old('meta_description', $course->meta_description) }}</textarea>
                            @error('meta_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="mb-3">
                            <label class="form-label">Content <span class="text-danger">*</span></label>
                            <textarea  name="description" class="ckeditor4">{{ old('description', $course->description) }}</textarea>
                            @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row sticky" id="courses_additinal_content">
                    <div class="col-md-12">
                        <div class="bg-indigo pt-1 pb-1 rounded-2">
                            <h4 class="text-center text-light" style="margin-bottom: 0px;">
                                Courses Additional Content
                            </h4>
                        </div>
                        <table class="table align-middle mb-3">
                            <tbody id="additionalContentContainer">
                                @php
                                $additionalTitles = old('courses_additional_title', $course->additionalContents->pluck('title')->toArray());
                                $additionalContents = old('courses_additional_content', $course->additionalContents->pluck('description')->toArray());
                                $additionalIds = old('courses_additional_id', $course->additionalContents->pluck('id')->toArray());
                                @endphp

                                @if(count($additionalTitles) > 0)
                                @foreach($additionalTitles as $index => $title)
                                <tr class="paragraph-row">
                                    <td style="width: 50%">
                                        <input type="hidden" name="courses_additional_id[]" value="{{ $additionalIds[$index] ?? '' }}">
                                        <label class="form-label" for="title">
                                            Courses Additional Title
                                        </label>
                                        <input type="text" name="courses_additional_title[]" class="form-control @error('courses_additional_title.'.$index) is-invalid @enderror" placeholder="Enter Courses Additional Title" value="{{ $title }}">
                                        @error('courses_additional_title.'.$index)
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </td>
                                    <td>
                                        <label class="form-label" for="title">
                                            Courses Additional Content
                                        </label>
                                        <textarea name="courses_additional_content[]" class="ckeditor4">{{ $additionalContents[$index] ?? '' }}</textarea>
                                        @error('courses_additional_content.'.$index)
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <button type="button" class="btn btn-danger btn-sm remove-paragraph mt-2">Remove</button>
                                    </td>
                                </tr>
                                @endforeach
                                @else
                                <tr class="paragraph-row">
                                    <td style="width: 50%">
                                        <input type="hidden" name="courses_additional_id[]" value="">
                                        <label class="form-label" for="title">
                                            Courses Additional Title
                                        </label>
                                        <input type="text" name="courses_additional_title[]" class="form-control" placeholder="Enter Courses Additional Title">
                                    </td>
                                    <td>
                                        <label class="form-label" for="title">
                                            Courses Additional Content
                                        </label>
                                        <textarea name="courses_additional_content[]" class="ckeditor4"></textarea>
                                        <button type="button" class="btn btn-danger btn-sm remove-paragraph mt-2" style="display: none;">Remove</button>
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end">
                                        <button class="btn btn-primary add_more_additional btn-sm" type="button">Add More Additional Content</button>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="row sticky" id="courses_highlights_content">
                    <div class="col-md-12">
                        <div class="bg-indigo pt-1 pb-1 rounded-2">
                            <h4 class="text-center text-light" style="margin-bottom: 0px;">
                                Courses Highlights Content
                            </h4>
                        </div>
                        <table class="table align-middle mb-3">
                            <tbody id="highlightsContainer">
                                @php
                                $highlightsContents = old('courses_highlights_content', $course->highlightsContents->pluck('content')->toArray());
                                $highlightsIcons = old('courses_highlights_icon', $course->highlightsContents->pluck('icon')->toArray());
                                $highlightsIds = old('courses_highlights_id', $course->highlightsContents->pluck('id')->toArray());
                                @endphp

                                @if(count($highlightsContents) > 0)
                                @foreach($highlightsContents as $index => $content)
                                <tr class="paragraph-row">
                                    <td style="width: 50%">
                                        <input type="hidden" name="courses_highlights_id[]" value="{{ $highlightsIds[$index] ?? '' }}">
                                        <label class="form-label" for="title">
                                            Courses Highlights Content
                                        </label>
                                        <input type="text" name="courses_highlights_content[]" class="form-control @error('courses_highlights_content.'.$index) is-invalid @enderror" placeholder="Enter Courses Highlights Content" value="{{ $content }}">
                                        @error('courses_highlights_content.'.$index)
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </td>
                                    <td>
                                        <label class="form-label" for="title">
                                            Courses Highlights Icon
                                        </label>
                                        <select name="courses_highlights_icon[]" class="form-control @error('courses_highlights_icon.'.$index) is-invalid @enderror">
                                            <option value="">Select Icon</option>
                                            @foreach($icons as $icon)
                                            <option value="{{ $icon->class }}" {{ ($highlightsIcons[$index] ?? '') == $icon->class ? 'selected' : '' }}>
                                                {{ $icon->name }} ({{ $icon->class }})
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('courses_highlights_icon.'.$index)
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror

                                        <button type="button" class="btn btn-danger btn-sm remove-paragraph mt-2">Remove</button>
                                    </td>
                                </tr>
                                @endforeach
                                @else
                                <tr class="paragraph-row">
                                    <td style="width: 50%">
                                        <input type="hidden" name="courses_highlights_id[]" value="">
                                        <label class="form-label" for="title">
                                            Courses Highlights Content
                                        </label>
                                        <input type="text" name="courses_highlights_content[]" class="form-control" placeholder="Enter Courses Highlights Content">
                                    </td>
                                    <td>
                                        <label class="form-label" for="title">
                                            Courses Highlights Icon
                                        </label>
                                        <select name="courses_highlights_icon[]" class="form-control">
                                            <option value="">Select Icon</option>
                                            @foreach($icons as $icon)
                                            <option value="{{ $icon->class }}">
                                                {{ $icon->name }} ({{ $icon->class }})
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-danger btn-sm remove-paragraph mt-2" style="display: none;">Remove</button>
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end">
                                        <button class="btn btn-primary add_more_highlights btn-sm" type="button">Add More Courses Highlights Content</button>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-lg-12">
                        <div class="d-flex align-items-center justify-content-end mb-4">
                            <a href="{{ route('manage-courses.index') }}" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary" id="submitButton">
                                <span id="submitText">Update Course</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
@push('scripts')
<script src="{{asset('backend/assets/js/pages/courses.js')}}"></script>
<script src="{{ asset('backend/assets/ckeditor-4/ckeditor.js') }}"></script>
<script>
    document.querySelectorAll('.ckeditor4').forEach(function(el) {
        CKEDITOR.replace(el, {
            removePlugins: 'exportpdf'
        });
    });
</script>
@endpush