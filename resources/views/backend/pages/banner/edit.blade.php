@extends('backend.layouts.master')
@section('title','Edit banner')
@push('styles')
<link rel="stylesheet" href="{{asset('backend/assets/plugins/bootstrap-tagsinput/bootstrap-tagsinput.css')}}">
<link rel="stylesheet" href="{{asset('backend/assets/plugins/tabler-icons/tabler-icons.css')}}">
<link rel="stylesheet" href="{{asset('backend/assets/css/dataTables.bootstrap5.min.css')}}">
@endpush
@section('main-content')
<div class="content">
    <div class="page-header">
        <div class="add-item d-flex">
            <div class="page-title">
                <h4 class="fw-bold"></h4>
                <h6>
                    Edit Banner
                </h6>
                           
            </div>
        </div>
        
    </div>
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
            <a href="{{ route('manage-banner.index') }}" data-title="Go Back to Previous Page" data-bs-toggle="tooltip" class="btn btn-sm btn-info" data-bs-original-title="Go Back to Previous Page">
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
            <form action="{{ route('manage-banner.update', $banner->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-sm-6 col-12">
                        <div class="mb-3">
                            <label class="form-label" for="banner_heading_name">Banner Heading Name</label>
                            <textarea class="form-control" id="banner_heading_name" name="banner_heading_name">{{ old('banner_heading_name', $banner->banner_heading_name) }}</textarea>
                           
                        </div>
                    </div>
                    
                    <div class="col-sm-6 col-12">
                        <div class="mb-3">
                            <label class="form-label" for="banner_link">Banner Link</label>
                            <input type="text" class="form-control" name="banner_link" id="banner_link" value="{{ old('banner_link', $banner->banner_link) }}">
                            
                        </div>
                    </div>
                    <div class="col-sm-6 col-12">
                        <div class="mb-3">
                            <label class="form-label" for="banner_desktop_img">Banner Desktop Image</label>
                            <input type="file" class="form-control" name="banner_desktop_img" id="banner_desktop_img">
                            
                            @if($banner->banner_desktop_img)
                                <div class="mt-2">
                                    <img src="{{ asset('upload/banner/' . $banner->banner_desktop_img) }}" alt="Desktop Banner" class="img-thumbnail" style="max-height: 100px;">
                                    <p class="small text-muted mt-1">Current Image</p>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-6 col-12">
                        <div class="mb-3">
                            <label class="form-label" for="banner_mobile_img">Banner Mobile Image</label>
                            <input type="file" class="form-control" name="banner_mobile_img" id="banner_mobile_img">
                            
                            @if($banner->banner_mobile_img)
                                <div class="mt-2">
                                    <img src="{{ asset('upload/banner/' . $banner->banner_mobile_img) }}" alt="Mobile Banner" class="img-thumbnail" style="max-height: 100px;">
                                    <p class="small text-muted mt-1">Current Image</p>
                                </div>
                            @endif
                        </div>
                    </div>                    
                </div>                
                <div class="row">
                    <div class="col-lg-12">
                        <div class="d-flex align-items-center justify-content-end mb-4">
                            <a href="{{ route('manage-banner.index') }}" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
@push('scripts')

@endpush