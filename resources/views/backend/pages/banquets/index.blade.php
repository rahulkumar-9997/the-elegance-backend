@extends('backend.layouts.master')
@section('title','Banquets List')
@push('styles')
@endpush
@section('main-content')
<div class="content">
    <div class="page-header">
        <div class="add-item d-flex">
            <div class="page-title">
                <h4 class="fw-bold">Banquets List</h4>
            </div>
        </div>
        <!-- <div class="page-btn">
             <a  href="javascript:void(0)" 
                data-ajax-banquets-add-popup="true"
                data-size="lg" 
                data-title="Add new Banquets" 
                data-url="{{ route('manage-banquets.create') }}" 
                data-bs-toggle="tooltip" 
                title="Add new Banquets"  
                class="btn btn-primary">
                <i class="ti ti-circle-plus me-1"></i>
                Add New Banquets
            </a>
        </div> -->
    </div>

    <!-- /product list -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <div class="display-banquets-list-html">
                    @if(isset($banquetList) && $banquetList->count() > 0)
                        @include('backend.pages.banquets.partials.banquets-list', ['banquetList' => $banquetList])
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
<script src="{{asset('backend/assets/js/pages/banquets.js')}}" type="text/javascript"></script>
<script src="{{asset('backend/assets/js/pages/banquets-images-ajax.js')}}" type="text/javascript"></script>
@endpush