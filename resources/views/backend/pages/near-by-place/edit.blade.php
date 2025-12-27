@extends('backend.layouts.master')
@section('title', 'Edit Near By Place: ' . $place->title)
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
                    Edit Near By Place: {{ $place->title }}
                </h6>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
            <a href="{{ route('manage-near-by-place.index') }}" data-title="Go Back to Previous Page" data-bs-toggle="tooltip" class="btn btn-sm btn-orange" data-bs-original-title="Go Back to Previous Page">
                Back to List
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
            <form action="{{ route('manage-near-by-place.update', $place->id) }}" method="POST" enctype="multipart/form-data" id="placeEditForm">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-sm-4 col-12">
                        <div class="mb-3">
                            <label class="form-label" for="title">
                                Title <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror"
                                id="title" name="title" value="{{ old('title', $place->title) }}"
                                placeholder="Enter title" onkeyup="generateSlug()" />
                            @error('title')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror

                            <!-- Current Slug Display -->
                            <div class="mt-2">
                                <small class="text-muted">Current Slug:</small>
                                <div class="alert alert-light py-1 px-2 mt-1">
                                    <code>{{ $place->slug }}</code>
                                </div>
                            </div>

                            <!-- New Slug Preview -->
                            <div id="slugPreview" class="mt-2" style="display: none;">
                                <small class="text-muted">New URL Slug Preview:</small>
                                <div class="alert alert-light py-1 px-2 mt-1">
                                    <code id="slugText"></code>
                                </div>
                                <small class="text-danger" id="slugWarning" style="display: none;">
                                    <i class="fas fa-exclamation-triangle"></i> This slug already exists!
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-4 col-12">
                        <div class="mb-3">
                            <label class="form-label" for="short_desc">
                                Short Description
                            </label>
                            <textarea class="form-control @error('short_desc') is-invalid @enderror"
                                id="short_desc" name="short_desc" rows="2"
                                placeholder="Enter short description">{{ old('short_desc', $place->short_desc) }}</textarea>
                            @error('short_desc')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-sm-4 col-12">
                        <div class="mb-3">
                            <label class="form-label" for="image">
                                Main Image
                            </label>
                            <input type="file" class="form-control @error('image') is-invalid @enderror"
                                name="image" id="image" accept="image/*" />
                            @error('image')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Leave blank to keep current image. Recommended size: 800x600px, Max size: 2MB</small>

                            <!-- Current Image Preview -->
                            @if($place->image)
                            <div class="mt-2">
                                <small class="text-muted">Current Image:</small>
                                <div class="mt-1">
                                    <img src="{{ asset('storage/nearby-places/' . $place->image) }}"
                                        alt="{{ $place->title }}"
                                        class="img-thumbnail"
                                        width="70">
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="col-sm-4 col-12">
                        <div class="mb-3">
                            <label class="form-label" for="meta_title">Meta title</label>
                            <input type="text" class="form-control @error('meta_title') is-invalid @enderror"
                                name="meta_title" id="meta_title"
                                value="{{ old('meta_title', $place->meta_title) }}"
                                placeholder="Enter meta title" />
                            @error('meta_title')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-sm-4 col-12">
                        <div class="mb-3">
                            <label class="form-label" for="meta_description">
                                Meta Description
                            </label>
                            <textarea class="form-control @error('meta_description') is-invalid @enderror"
                                id="meta_description" name="meta_description" rows="2"
                                placeholder="Enter meta description">{{ old('meta_description', $place->meta_description) }}</textarea>
                            @error('meta_description')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-sm-4 col-12">
                        <div class="mb-3 mt-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch"
                                    id="status" name="status" value="1"
                                    {{ old('status', $place->status) ? 'checked' : '' }}>
                                <label class="form-check-label" for="status">Status</label>
                            </div>
                            @error('status')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="summer-description-box mb-3">
                            <label class="form-label">Content <span class="text-danger">*</span></label>
                            <textarea class="ckeditor4 @error('long_description') is-invalid @enderror"
                                name="long_description" hidden>{{ old('long_description', $place->long_description) }}</textarea>
                            @error('long_description')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="d-flex align-items-center justify-content-end mb-4">
                            <a href="{{ route('manage-near-by-place.index') }}" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary" id="submitButton">
                                <span id="submitText">Update</span>
                                <span id="submitSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
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
    document.getElementById('blogFormAdd').addEventListener('submit', function() {
        const submitButton = document.getElementById('submitButton');
        const submitText = document.getElementById('submitText');
        const submitSpinner = document.getElementById('submitSpinner');
        submitButton.disabled = true;
        submitText.textContent = 'Processing...';
        submitSpinner.classList.remove('d-none');
    });
</script>


@endpush