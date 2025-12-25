@extends('backend.layouts.master')
@section('title','Video List')
@push('styles')

@endpush
@section('main-content')
<div class="content">
    <div class="page-header">
        <div class="add-item d-flex">
            <div class="page-title">
                <h4 class="fw-bold">Video List</h4>
            </div>
        </div>
        <div class="page-btn">
             <a  href="javascript:void(0)" 
                data-ajax-video-add-popup="true"
                data-size="lg" 
                data-title="Add new Video" 
                data-url="{{ route('manage-video.create') }}" 
                data-bs-toggle="tooltip" 
                title="Add new Video"  
                class="btn btn-primary">
                <i class="ti ti-circle-plus me-1"></i>
                Add New Video
            </a>
        </div>
    </div>

    <!-- /product list -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <div class="display-video-list-html">
                    @if(isset($videoList) && $videoList->count() > 0)
                        @include('backend.pages.video.partials.video-list', ['videoList' => $videoList])
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- /product list -->
</div>
<!-- Modal -->
@include('backend.layouts.common-modal-form')
<!-- modal--->
@endsection
@push('scripts')
<script src="{{asset('backend/assets/js/pages/video.js')}}" type="text/javascript"></script>
@endpush
