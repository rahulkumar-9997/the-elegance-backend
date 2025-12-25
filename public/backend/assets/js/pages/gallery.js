$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

var site_url = $('meta[name="base-url"]').attr('content');
$(document).ready(function () {
    $(document).on('click', 'a[data-ajax-gallery-add-popup="true"]', function () {
        var title = $(this).data('title');
        var size = ($(this).data('size') == '') ? 'md' : $(this).data('size');
        var action = ($(this).data('action') == '') ? '' : $(this).data('action');
        var url = $(this).data('url');
        var data = {
            size: size,
            url: url,
            action: action
        };
        $("#commanModel .modal-title").html(title);
        $("#commanModel .modal-dialog").addClass('modal-' + size);

        $.ajax({
            url: url,
            type: 'GET',
            data: data,
            success: function (data) {
                $('#commanModel .render-data').html(data.form);
                $("#commanModel").modal('show');
                
            },
            error: function (data) {
                data = data.responseJSON;
            }
        });
    });

    $(document).off('submit', '#galleryAddForm').on('submit', '#galleryAddForm', function (event) {
        event.preventDefault();
        var form = $(this);
        var submitButton = form.find('button[type="submit"]');
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        submitButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');
        var formData = new FormData(this);
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                submitButton.prop('disabled', false);
                submitButton.html('Save changes');
                if (response.status === 'success') {                    
                    $('.display-gallery-list-html').html(response.galleryListData);
                    feather.replace();
                    form[0].reset();
                    $('#commanModel').modal('hide');
                    Toastify({
                        text: response.message,
                        duration: 10000,
                        gravity: "top",
                        position: "right",
                        className: "bg-success",
                        escapeMarkup: false,
                        close: true,
                        onClick: function () { }
                    }).showToast();
                }
            },
            error: function (xhr, status, error) {
                if(xhr.responseJSON && xhr.responseJSON.message){
                    Toastify({
                        text: xhr.responseJSON.message,
                        duration: 10000,
                        gravity: "top",
                        position: "right",
                        className: "bg-danger",
                        escapeMarkup: false,
                        close: true,
                        onClick: function () { }
                    }).showToast();
                }
                submitButton.prop('disabled', false);
                submitButton.html('Save changes');
                var errors = xhr.responseJSON.errors;
                if (errors) {
                    $.each(errors, function (key, value) {
                        var errorElement = $('#' + key + '_error');
                        if (errorElement.length) {
                            errorElement.text(value[0]);
                        }
                        var inputField = $('#' + key);
                        inputField.addClass('is-invalid');
                        inputField.after('<div class="invalid-feedback">' + value[0] + '</div>');
                    });
                }
            }
        });
    });

    $(document).on('click', 'a[data-ajax-edit-gallery="true"]', function () {
        var title = $(this).data('title');
        var size = ($(this).data('size') == '') ? 'md' : $(this).data('size');
        var url = $(this).data('url');
        var data = {
            size: size,
            url: url
        };
        $("#commanModel .modal-title").html(title);
        $("#commanModel .modal-dialog").addClass('modal-' + size);

        $.ajax({
            url: url,
            type: 'GET',
            data: data,
            success: function (data) {
                $('#commanModel .render-data').html(data.form);
                $("#commanModel").modal('show');
                
            },
            error: function (data) {
                data = data.responseJSON;
            }
        });
    });

    $(document).off('submit', '#galleryEditForm').on('submit', '#galleryEditForm', function (event) {
        event.preventDefault();
        var form = $(this);
        var submitButton = form.find('button[type="submit"]');
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        submitButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');
        var formData = new FormData(this);
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                submitButton.prop('disabled', false);
                submitButton.html('Save changes');
                if (response.status === 'success') {                    
                    $('.display-gallery-list-html').html(response.galleryListData);
                    feather.replace();
                    form[0].reset();
                    $('#commanModel').modal('hide');
                    Toastify({
                        text: response.message,
                        duration: 10000,
                        gravity: "top",
                        position: "right",
                        className: "bg-success",
                        escapeMarkup: false,
                        close: true,
                        onClick: function () { }
                    }).showToast();
                }
            },
            error: function (xhr, status, error) {
                submitButton.prop('disabled', false);
                submitButton.html('Save changes');
                var errors = xhr.responseJSON.errors;
                if (errors) {
                    $.each(errors, function (key, value) {
                        var errorElement = $('#' + key + '_error');
                        if (errorElement.length) {
                            errorElement.text(value[0]);
                        }
                        var inputField = $('#' + key);
                        inputField.addClass('is-invalid');
                        inputField.after('<div class="invalid-feedback">' + value[0] + '</div>');
                    });
                }
            }
        });
    });

    $(document).on('click', '.show_confirm', function (e) {
        e.preventDefault();
        var form = $(this).closest('form');
        var albumName = $(this).data('name');
        Swal.fire({
            title: `Are you sure you want to delete this ${albumName}?`,
            text: "If you delete this, it will be gone forever.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, delete it!",
            cancelButtonText: "Cancel",
            dangerMode: true,
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: {
                        _method: 'DELETE',
                        _token: form.find('input[name="_token"]').val()
                    },
                    success: function (response) {
                        if (response.status === 'success') {
                            $('.display-gallery-list-html').html(response.galleryListData);
                            feather.replace();
                            Toastify({
                                text: response.message,
                                duration: 10000,
                                gravity: "top",
                                position: "right",
                                className: "bg-success",
                                escapeMarkup: false,
                                close: true,
                            }).showToast();
                        }
                    },
                    error: function (xhr) {
                        Toastify({
                            text: 'Failed to delete disclosure',
                            duration: 10000,
                            gravity: "top",
                            position: "right",
                            className: "bg-danger",
                            escapeMarkup: false,
                            close: true,
                        }).showToast();
                    }
                });
            }
        });
    });

});


