@extends('backend.layouts.master')
@section('title','Edit new blog')
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
                    Edit new Blog
                </h6>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
            <a href="{{ route('manage-blog.index') }}" data-title="Go Back to Previous Page" data-bs-toggle="tooltip" class="btn btn-sm btn-info" data-bs-original-title="Go Back to Previous Page">
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
            <form action="{{ route('manage-blog.update', $blog->id) }}" method="POST" enctype="multipart/form-data" id="blogFormEdit">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-sm-4 col-12">
                        <div class="mb-3">
                            <label class="form-label" for="title">
                                Title <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" id="banner_video_title"
                                name="title" value="{{ old('title', $blog->title) }}" />
                            @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-sm-4 col-12">
                        <div class="mb-3">
                            <label class="form-label" for="short_description">
                                Short Description
                            </label>
                            <textarea class="form-control @error('short_description') is-invalid @enderror" id="short_description" rows="2"
                                name="short_description" rows="2">{{ old('short_description', $blog->short_desc) }}</textarea>
                            @error('short_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-sm-4 col-12">
                        <div class="mb-3">
                            <label class="form-label" for="main_image">
                                Main Image
                                @if(!$blog->featured_image)
                                <span class="text-danger">*</span>
                                @endif
                            </label>
                            <input type="file" class="form-control @error('main_image') is-invalid @enderror"
                                name="main_image" id="main_image" />
                            @if($blog->featured_image)
                            <div class="mt-2">
                                <img src="{{ asset('upload/blog/'.$blog->featured_image) }}" width="100" class="img-thumbnail">
                                <input type="hidden" name="existing_main_image" value="{{ $blog->featured_image }}">
                            </div>
                            @endif
                            @error('main_image')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-sm-4 col-12">
                        <div class="mb-3">
                            <label class="form-label" for="meta_title">Meta title</label>
                            <input type="text" class="form-control @error('meta_title') is-invalid @enderror"
                                name="meta_title" id="meta_title" value="{{ old('meta_title', $blog->meta_title) }}" />
                            @error('meta_title')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-sm-4 col-12">
                        <div class="mb-3">
                            <label class="form-label" for="meta_description">
                                Meta Description
                            </label>
                            <textarea class="form-control @error('meta_description') is-invalid @enderror"
                                id="meta_description" name="meta_description" rows="2">{{ old('meta_description', $blog->meta_description) }}</textarea>
                            @error('meta_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-sm-4 col-12">
                        <div class="mb-3">
                            <label class="form-label" for="more_image">
                                Blog more images (Select multiple)
                            </label>
                            <input type="file" class="form-control @error('more_image') is-invalid @enderror"
                            name="more_image[]" id="more_image" multiple />
                            @error('more_image')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    @if($blog->images->count() > 0)
                    <div class="col-sm-12 col-12">
                        <div class="mt-2">
                            @foreach($blog->images as $image)
                            <div class="d-inline-block me-2 mb-2 position-relative">
                                <img src="{{ asset('upload/blog/'.$image->image) }}" width="80" class="img-thumbnail">
                                <input type="hidden" name="existing_more_images[]" value="{{ $image->id }}">
                                <button type="button" class="btn btn-danger btn-xs position-absolute top-0 end-0 remove-image"
                                    data-image-id="{{ $image->id }}">×</button>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    <div class="col-lg-12">
                        <div class="summer-description-box mb-3">
                            <label class="form-label">Content</label>
                            <textarea name="content" class="ckeditor4">
                                {{ old('meta_description', $blog->content) }}
                            </textarea>
                        </div>
                    </div>                    
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="summer-description-box mb-3">
                            <div class="form-check form-check-lg d-flex align-items-center">
                                <input class="form-check-input" type="checkbox" id="add_paragraphs" name="add_paragraphs" value="1"
                                    {{ old('add_paragraphs', $blog->paragraphs->count() > 0 ? '1' : '') ? 'checked' : '' }}>
                                <label class="form-check-label" for="checkebox-lg">
                                    Blog Paragraphs
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row sticky" id="blogParagraphsSection" style="display: {{ $blog->paragraphs->count() > 0 ? 'block' : 'none' }};">
                    <div class="col-md-12">
                        <div class="bg-indigo pt-1 pb-1 rounded-2">
                            <h4 class="text-center text-light" style="margin-bottom: 0px;">Blog Paragraphs</h4>
                        </div>
                        <table class="table align-middle mb-3">
                            <tbody id="paragraphsContainer">
                                @foreach($blog->paragraphs as $index => $paragraph)
                                <tr class="paragraph-row">
                                    <td style="width: 25%">
                                        <input type="text" name="paragraphs_title[]" class="form-control"
                                            value="{{ old('paragraphs_title.'.$index, $paragraph->title) }}"
                                            placeholder="Enter Paragraphs Title">
                                    </td>
                                    <td style="width: 25%">
                                        <input type="file" name="paragraphs_image[]" class="form-control">
                                        @if($paragraph->image)
                                        <div class="mt-2">
                                            <img src="{{ asset('upload/blog/'.$paragraph->image) }}" width="100">
                                            <input type="hidden" name="existing_paragraphs_image[]" value="{{ $paragraph->image }}">
                                        </div>
                                        @endif
                                    </td>
                                    <td>
                                        <textarea name="paragraphs_content[]" class="ckeditor4">{{ old('paragraphs_content.'.$index, $paragraph->content) }}</textarea>
                                        <input type="hidden" name="paragraph_ids[]" value="{{ $paragraph->id }}">
                                        <button type="button" class="btn btn-danger btn-sm remove-paragraph"
                                            style="{{ $blog->paragraphs->count() > 1 ? '' : 'display: none;' }}">Remove</button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end">
                                        <button class="btn btn-primary add-more-blog-paragraphs btn-sm" type="button">Add More Blog Paragraphs</button>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="d-flex align-items-center justify-content-end mb-4">
                            <a href="{{ route('manage-blog.index') }}" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary" id="submitButton">
                                Update
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
<script src="{{ asset('backend/assets/ckeditor-4/ckeditor.js') }}"></script>
<script>
    document.querySelectorAll('.ckeditor4').forEach(function(el) {
        CKEDITOR.replace(el, {
            removePlugins: 'exportpdf'
        });
    });
</script>
<script>
    $(document).ready(function() {
        $(document).on('click', '.remove-image', function() {
            const imageId = $(this).data('image-id');
            $(this).closest('div').remove();
            $('<input>').attr({
                type: 'hidden',
                name: 'removed_images[]',
                value: imageId
            }).appendTo('form');
        });
        $('#add_paragraphs').change(function() {
            $('#blogParagraphsSection').toggle($(this).is(':checked'));
        });
        let paragraphIndex = 1; 
        $(document).on('click', '.add-more-blog-paragraphs', function () {
            var newId = 'paragraph_' + paragraphIndex++;
            var rowCount = $('table tbody tr').length;
            var newRow = `
                <tr class="paragraph-row">
                    <td style="width: 25%">
                        <input type="text" name="paragraphs_title[]" class="form-control" placeholder="Enter Paragraphs Title">
                    </td>
                    <td style="width: 25%">
                        <input type="file" name="paragraphs_image[]" class="form-control">
                    </td>
                    <td>
                        <textarea name="paragraphs_content[]" id="${newId}" class="ckeditor4"></textarea>
                        <button type="button" class="btn btn-danger btn-sm remove-paragraph mt-2">Remove</button>
                    </td>
                </tr>
            `;
            $('#paragraphsContainer').append(newRow);
            CKEDITOR.replace(newId, {
                removePlugins: 'exportpdf'
            });
            $('#blogParagraphsSection').show();
        });

        $(document).on('click', '.remove-paragraph', function() {
            if ($('.paragraph-row').length > 1) {
                $(this).closest('.paragraph-row').remove();
            }
        });
    });
</script>

@endpush