$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

var site_url = $('meta[name="base-url"]').attr('content');
$(document).ready(function () {
    $(document).on('click', 'a[data-ajax-video-add-popup="true"]', function () {
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

    $(document).off('submit', '#videoAddForm').on('submit', '#videoAddForm', function (event) {
        event.preventDefault();
        var form = $(this);
        var submitButton = form.find('button[type="submit"]');
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        var formData = new FormData(this);
        $("#uploadProgressWrapper").show();
        $("#uploadProgress").css("width", "0%").text("0%");
        var xhr = null;
        $.ajax({
            xhr: function () {
                xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function (evt) {
                    if (evt.lengthComputable) {
                        var percentComplete = Math.round((evt.loaded / evt.total) * 100);
                        $("#uploadProgress").css("width", percentComplete + "%").text(percentComplete + "%");
                        if (percentComplete < 100) {
                            submitButton.html('<span class="spinner-border spinner-border-sm" role="status"></span> Uploading ' + percentComplete + '%');
                        }
                    }
                }, false);

                xhr.addEventListener("loadstart", function () {
                    console.log('Upload started');
                });

                xhr.addEventListener("error", function () {
                    console.log('Upload error');
                });

                xhr.addEventListener("abort", function () {
                    console.log('Upload aborted');
                });

                return xhr;
            },
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            timeout: 300000,
            beforeSend: function () {
                submitButton.prop('disabled', true)
                    .html('<span class="spinner-border spinner-border-sm" role="status"></span> Starting upload...');
                if (!$('#cancelUploadBtn').length) {
                    submitButton.after('<button type="button" id="cancelUploadBtn" class="btn btn-danger ms-2">Cancel</button>');
                    $('#cancelUploadBtn').click(function () {
                        if (xhr) {
                            xhr.abort();
                            resetFormState();
                            Toastify({
                                text: 'Upload cancelled',
                                duration: 3000,
                                gravity: "top",
                                position: "right",
                                className: "bg-warning",
                                close: true
                            }).showToast();
                        }
                    });
                }
            },
            success: function (response) {
                if (response && response.status) {
                    if (response.status === 'success') {
                        $("#uploadProgress").css("width", "100%").text("Upload Complete");

                        if (response.videoListData) {
                            $('.display-video-list-html').html(response.videoListData);
                        }

                        feather.replace();
                        form[0].reset();
                        $('#commanModel').modal('hide');

                        Toastify({
                            text: response.message || 'Video uploaded successfully!',
                            duration: 5000,
                            gravity: "top",
                            position: "right",
                            className: "bg-success",
                            close: true
                        }).showToast();

                    } else if (response.status === 'error') {
                        handleUploadError(response.message || 'Upload failed');
                    }
                } else {
                    handleUploadError('Invalid response from server');
                }

                resetFormState();
            },
            error: function (xhr, status, error) {
                var errorMessage = 'An error occurred during upload';

                if (status === 'timeout') {
                    errorMessage = 'Upload timed out. Please try again.';
                } else if (status === 'abort') {
                    errorMessage = 'Upload was cancelled.';
                    console.log('Upload aborted by user');
                    return;
                } else if (xhr.status === 413) {
                    errorMessage = 'File is too large. Please choose a smaller video.';
                } else if (xhr.status === 422) {
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        displayValidationErrors(xhr.responseJSON.errors);
                        errorMessage = xhr.responseJSON.message || 'Please fix the validation errors';
                    }
                } else if (xhr.status === 500) {
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else {
                        errorMessage = 'Server error. Please try again later.';
                    }
                } else if (xhr.status === 0) {
                    errorMessage = 'Network connection lost. Please check your internet connection.';
                }
                handleUploadError(errorMessage);
            },
            complete: function () {
                $('#cancelUploadBtn').remove();
            }
        });

        function handleUploadError(message) {
            $("#uploadProgress").css("width", "0%").text("Upload Failed");
            $("#uploadProgress").removeClass('progress-bar-animated');
            Toastify({
                text: message,
                duration: 8000, 
                gravity: "top",
                position: "right",
                className: "bg-danger",
                close: true,
                stopOnFocus: true
            }).showToast();

            resetFormState();
        }

        function displayValidationErrors(errors) {
            $.each(errors, function (field, messages) {
                var input = form.find('[name="' + field + '"]');
                var feedbackElement = input.closest('.form-group').find('.invalid-feedback') || input.next('.invalid-feedback');

                input.addClass('is-invalid');
                if (feedbackElement.length === 0) {
                    input.after('<div class="invalid-feedback">' + messages[0] + '</div>');
                } else {
                    feedbackElement.text(messages[0]);
                }
            });
        }

        function resetFormState() {
            submitButton.prop('disabled', false).html('Submit');
            setTimeout(function () {
                $("#uploadProgressWrapper").hide();
                $("#uploadProgress").addClass('progress-bar-animated');
            }, 3000);
        }
    });


    $(document).on('click', 'a[data-ajax-edit-video="true"]', function () {
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

    $(document).off('submit', '#videoEditForm').on('submit', '#videoEditForm', function (event) {
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
                submitButton.prop('disabled', false).html('Save changes');
                if (response && response.status) {
                    //alert(JSON.stringify(response));
                    if (response.status === 'success') {
                        if (response.videoListData) {
                            $('.display-video-list-html').html(response.videoListData);
                        }
                        feather.replace();
                        form[0].reset();
                        $('#commanModel').modal('hide');
                        Toastify({
                            text: response.message || 'Video updated successfully!',
                            duration: 10000,
                            gravity: "top",
                            position: "right",
                            className: "bg-success",
                            escapeMarkup: false,
                            close: true,
                            onClick: function () { }
                        }).showToast();

                    } else if (response.status === 'error') {
                        Toastify({
                            text: response.message || 'Update failed!',
                            duration: 5000,
                            gravity: "top",
                            position: "right",
                            className: "bg-danger",
                            close: true
                        }).showToast();
                    }
                } else {
                    Toastify({
                        text: 'Invalid response from server',
                        duration: 5000,
                        gravity: "top",
                        position: "right",
                        className: "bg-warning",
                        close: true
                    }).showToast();
                }
            },
            error: function (xhr) {
                submitButton.prop('disabled', false).html('Save changes');
                let errorMessage = 'An error occurred while updating the video';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.errors) {
                        errorMessage = 'Validation failed: ' + Object.values(xhr.responseJSON.errors).join(', ');
                    }
                } else if (xhr.status === 0) {
                    errorMessage = 'Network error - please check your connection';
                } else if (xhr.status === 500) {
                    errorMessage = 'Server error - please try again later';
                }

                Toastify({
                    text: errorMessage,
                    duration: 5000,
                    gravity: "top",
                    position: "right",
                    className: "bg-danger",
                    close: true
                }).showToast();
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    $.each(xhr.responseJSON.errors, function (field, errors) {
                        var input = form.find('[name="' + field + '"]');
                        input.addClass('is-invalid');
                        input.after('<div class="invalid-feedback">' + errors[0] + '</div>');
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
                            $('.display-video-list-html').html(response.videoListData);
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


