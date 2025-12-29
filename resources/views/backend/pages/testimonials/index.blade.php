@extends('backend.layouts.master')
@section('title','Testimonials List')
@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush
@section('main-content')
<div class="content">
    <div class="page-header">
        <div class="add-item d-flex">
            <div class="page-title">
                <h4 class="fw-bold">Testimonials List</h4>
            </div>
        </div>
        <div class="page-btn">
             <a  href="javascript:void(0)" 
                data-ajax-testimonials-add-popup="true"
                data-size="lg" 
                data-title="Add new Testimonials" 
                data-url="{{ route('manage-testimonials.create') }}" 
                data-bs-toggle="tooltip" 
                title="Add new Testimonials"  
                class="btn btn-primary">
                <i class="ti ti-circle-plus me-1"></i>
                Add New Testimonials
            </a>
        </div>
    </div>

    <!-- /product list -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <div class="display-gallery-list-html">
                    @if(isset($testimonialList) && $testimonialList->count() > 0)
                        @include('backend.pages.testimonials.partials.testimonials-list', ['testimonialList' => $testimonialList])
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
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="{{asset('backend/assets/js/pages/testimonial.js')}}" type="text/javascript"></script>
@endpush