@extends('backend.layouts.master')
@section('title','Near By Place List')
@push('styles')

@endpush
@section('main-content')
<div class="content">
    <div class="page-header">
        <div class="add-item d-flex">
            <div class="page-title">
                <h4 class="fw-bold">Near By Place List</h4>
            </div>
        </div>
        <div class="page-btn">
            <a href="{{ route('manage-near-by-place.create') }}"
                data-title="Add New Near By Place"
                data-bs-toggle="tooltip"
                title="Add New Near By Place"
                class="btn btn-orange">
                <i class="ti ti-circle-plus me-1"></i>
                Add New Near By Place
            </a>
        </div>
    </div>
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <div class="display-gallery-list-html">
                    @if(isset($nearByPlaceList) && $nearByPlaceList->count() > 0)
                    @include('backend.pages.near-by-place.partials.near-by-place-list', ['nearByPlaceList' => $nearByPlaceList])
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
<script>
    $(document).ready(function() {
        /* Delete Confirmation */
        $('.delete-btn').click(function(event) {
            var form = $(this).closest("form");
            var name = $(this).data("name");
            event.preventDefault();

            Swal.fire({
                title: `Are you sure you want to delete "${name}"?`,
                text: "This action cannot be undone. The place will be permanently removed.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Yes, delete it!",
                cancelButtonText: "Cancel",
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });

        /* Add to Attraction Confirmation */
        $('.show_confirm_add_attraction').click(function(event) {
            var form = $(this).closest("form");
            var name = $(this).data("name");
            event.preventDefault();

            Swal.fire({
                title: `Add "${name}" to Attractions?`,
                text: "This place will be marked as a tourist attraction.",
                icon: "info",
                showCancelButton: true,
                confirmButtonColor: "#0dcaf0",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "Yes, add to attractions!",
                cancelButtonText: "Cancel",
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });

        /* Remove from Attraction Confirmation */
        $('.show_confirm_remove_attraction').click(function(event) {
            var form = $(this).closest("form");
            var name = $(this).data("name");
            event.preventDefault();

            Swal.fire({
                title: `Remove "${name}" from Attractions?`,
                text: "This place will no longer be marked as a tourist attraction.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#ffc107",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "Yes, remove from attractions!",
                cancelButtonText: "Cancel",
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });

        /* Order Up/Down Confirmation*/
        $('.show_confirm_order').click(function(event) {
            var form = $(this).closest("form");
            var name = $(this).data("name");
            var direction = $(this).data("direction");
            var directionText = direction === 'up' ? 'up' : 'down';
            event.preventDefault();

            Swal.fire({
                title: `Move "${name}" ${directionText}?`,
                text: `This will change the display order of this place.`,
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#6c757d",
                cancelButtonColor: "#6c757d",
                confirmButtonText: `Yes, move ${directionText}!`,
                cancelButtonText: "Cancel",
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });

    });
</script>
@endpush