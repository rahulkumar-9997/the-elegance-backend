@extends('backend.layouts.master')
@section('title','Banner List')
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
                <h6>Manage Banner</h6>
            </div>
        </div>
        <div class="page-btn">
            <a href="{{ route('manage-banner.create') }}" class="btn btn-primary">
                <i class="ti ti-circle-plus me-1"></i>
                Create New Banner
            </a>
        </div>
    </div>
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
            <div class="search-set">
                <div class="search-input">
                    <span class="btn-searchset">
                        <i class="ti ti-search fs-14 feather-search"></i>
                    </span>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table datatable">
                    <thead class="thead-light">
                        <tr>
                            <th>Heading</th>
                            <th>Content</th>
                            <th>Link</th>
                            <th>Desktop Image</th>
                            <th>Mobile Image</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($banners->count() > 0)
                        @foreach($banners as $banner)
                        <tr>
                            <td>{!! $banner->banner_heading_name !!}</td>
                            <td>{!! $banner->banner_content !!}</td>
                            <td>{{ $banner->banner_link ?? '-' }}</td>
                            <td>
                                @if($banner->banner_desktop_img)
                                <img src="{{ asset('upload/banner/' . $banner->banner_desktop_img) }}" width="100">
                                @else
                                -
                                @endif
                            </td>
                            <td>
                                @if($banner->banner_mobile_img)
                                    <img src="{{ asset('upload/banner/' . $banner->banner_mobile_img) }}" width="100">
                                @else
                                -
                                @endif
                            </td>
                            <td class="action-table-data">
                                <div class="edit-delete-action">
                                    <a class="btn btn-sm btn-primary me-2 p-2" href="{{ route('manage-banner.edit', $banner->id) }}">
                                        <i data-feather="edit" class="feather-edit"></i>
                                    </a>
                                    <form action="{{ route('manage-banner.destroy', $banner->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger show_confirm" data-name="Delete Banner">
                                            <i data-feather="trash-2" class="feather-trash-2"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        @else
                        <tr>
                            <td colspan="6" class="text-center">No banners found.</td>
                        </tr>
                        @endif
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</div>

@endsection
@push('scripts')
<script>
    $(document).ready(function() {
        $('.show_confirm').click(function(event) {
            var form = $(this).closest("form");
            var name = $(this).data("name");
            event.preventDefault();

            Swal.fire({
                title: `Are you sure you want to delete this ${name}?`,
                text: "If you delete this, it will be gone forever.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, delete it!",
                cancelButtonText: "Cancel",
                dangerMode: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });

    });
</script>
@endpush