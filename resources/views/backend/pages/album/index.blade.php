@extends('backend.layouts.master')
@section('title','Manage Album')
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
                <h6>Manage Album</h6>
            </div>
        </div>
        <div class="page-btn">
            <a  href="javascript:void(0)" 
                data-ajax-album-add="true"
                data-size="lg" 
                data-title="Add new Album"
                data-action="normal_album" 
                data-url="{{ route('manage-album.create') }}" 
                data-bs-toggle="tooltip" 
                title="Add new Album"  
                class="btn btn-primary">
                <i class="ti ti-circle-plus me-1"></i>
                Add New Album
            </a>
        </div>
    </div>
    <div class="card">        
        <div class="card-body p-0">
            <div class="table-responsive" id="album_list">                
                @include('backend.pages.album.partials.album-list',['albums' => $albums] )
            </div>
        </div>
    </div>
</div>

@endsection
@push('scripts')
@push('scripts')
<script src="{{asset('backend/assets/js/pages/album.js')}}"></script>
@endpush
@endpush