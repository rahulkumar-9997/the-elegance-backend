@extends('backend.layouts.master')
@section('title','Tafri Image List')
@push('styles')
@endpush
@section('main-content')
<div class="content">
    <div class="page-header">
        <div class="add-item d-flex">
            <div class="page-title">
                <h4 class="fw-bold">Tafri Image List</h4>
            </div>
        </div>
        <div class="page-btn">
             <a  href="javascript:void(0)" 
                data-ajax-tafri-image-add-popup="true"
                data-size="lg" 
                data-title="Add new Tafri Image" 
                data-url="{{ route('manage-tafri-lounge-image.create') }}" 
                data-bs-toggle="tooltip" 
                title="Add new Tafri Image"  
                class="btn btn-primary">
                <i class="ti ti-circle-plus me-1"></i>
                Add new Tafri Image
            </a>
        </div>
    </div>

    <!-- /product list -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <div class="display-tafri-image-list-html">
                    @if(isset($tafriImageList) && $tafriImageList->count() > 0)
                        @include('backend.pages.tafri-image.partials.image-list', ['tafriImageList' => $tafriImageList])
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
<script src="{{asset('backend/assets/js/pages/tafri-image.js')}}" type="text/javascript"></script>
@endpush