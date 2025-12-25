@extends('backend.layouts.master')
@section('title','Awards List')
@push('styles')

@endpush
@section('main-content')
<div class="content">
    <div class="page-header">
        <div class="add-item d-flex">
            <div class="page-title">
                <h4 class="fw-bold">Awards List</h4>
            </div>
        </div>
        <div class="page-btn">
             <a  href="javascript:void(0)" 
                data-ajax-awards-add-popup="true"
                data-size="lg" 
                data-title="Add new Awards" 
                data-url="{{ route('manage-awards.create') }}" 
                data-bs-toggle="tooltip" 
                title="Add new Awards"  
                class="btn btn-primary">
                <i class="ti ti-circle-plus me-1"></i>
                Add New Awards
            </a>
        </div>
    </div>

    <!-- /product list -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <div class="display-awards-list-html">
                    @if(isset($awardsList) && $awardsList->count() > 0)
                        @include('backend.pages.awards.partials.awards-list', ['awardsList' => $awardsList])
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modal -->
@include('backend.layouts.common-modal-form')
<!-- modal--->
@endsection
@push('scripts')
<script src="{{asset('backend/assets/js/pages/awards.js')}}" type="text/javascript"></script>
@endpush