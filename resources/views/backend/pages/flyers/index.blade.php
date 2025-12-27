@extends('backend.layouts.master')
@section('title','Flyers List')
@push('styles')

@endpush
@section('main-content')
<div class="content">
    <div class="page-header">
        <div class="add-item d-flex">
            <div class="page-title">
                <h4 class="fw-bold">Flyers List</h4>
            </div>
        </div>
        <div class="page-btn">
             <a  href="javascript:void(0)" 
                data-ajax-gallery-add-popup="true"
                data-size="lg" 
                data-title="Add new Flyers" 
                data-url="{{ route('manage-flyers.create') }}" 
                data-bs-toggle="tooltip" 
                title="Add new Flyers"  
                class="btn btn-primary">
                <i class="ti ti-circle-plus me-1"></i>
                Add New Flyers
            </a>
        </div>
    </div>

    <!-- /product list -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <div class="display-flyers-list-html">
                    @if(isset($flyersList) && $flyersList->count() > 0)
                        @include('backend.pages.flyers.partials.flyers-list', ['flyersList' => $flyersList])
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
<script src="{{asset('backend/assets/js/pages/flyers.js')}}" type="text/javascript"></script>
@endpush