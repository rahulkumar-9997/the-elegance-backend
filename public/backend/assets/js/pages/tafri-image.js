$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});

var site_url = $('meta[name="base-url"]').attr("content");
$(document).ready(function () {
    $(document).on(
        "click",
        'a[data-ajax-tafri-image-add-popup="true"]',
        function () {
            var title = $(this).data("title");
            var data_action = $(this).data("action");
            var size = $(this).data("size") == "" ? "md" : $(this).data("size");
            var action =
                $(this).data("action") == "" ? "" : $(this).data("action");
            var url = $(this).data("url");
            var data = {
                size: size,
                url: url,
                action: action,
                data_action: data_action,
            };
            $("#commanModel .modal-title").html(title);
            $("#commanModel .modal-dialog").addClass("modal-" + size);

            $.ajax({
                url: url,
                type: "GET",
                data: data,
                success: function (data) {
                    $("#commanModel .render-data").html(data.form);
                    $("#commanModel").modal("show");
                },
                error: function (data) {
                    data = data.responseJSON;
                },
            });
        }
    );

    $(document)
        .off("submit", "#tafriImageAddForm")
        .on("submit", "#tafriImageAddForm", function (event) {
            event.preventDefault();
            var form = $(this);
            var submitButton = form.find('button[type="submit"]');
            $(".form-control").removeClass("is-invalid");
            $(".invalid-feedback").remove();
            submitButton
                .prop("disabled", true)
                .html(
                    '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...'
                );
            var formData = new FormData(this);
            $.ajax({
                url: form.attr("action"),
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    submitButton.prop("disabled", false);
                    submitButton.html("Save changes");
                    if (response.status === true) {
                        form[0].reset();
                        $("#commanModel").modal("hide");
                        $(".display-tafri-image-list-html").html(response.html);
                        feather.replace();
                        Toastify({
                            text: response.message,
                            duration: 5000,
                            gravity: "top",
                            position: "right",
                            className: "bg-success",
                            close: true,
                        }).showToast();
                    }
                },
                error: function (xhr, status, error) {
                    submitButton.prop("disabled", false);
                    submitButton.html("Submit");
                    var errors = xhr.responseJSON.errors;
                    if (errors) {
                        $.each(errors, function (key, value) {
                            var errorElement = $("#" + key + "_error");
                            if (errorElement.length) {
                                errorElement.text(value[0]);
                            }
                            var inputField = $("#" + key);
                            inputField.addClass("is-invalid");
                            inputField.after(
                                '<div class="invalid-feedback">' +
                                    value[0] +
                                    "</div>"
                            );
                        });
                    }
                },
            });
        });

    /*  Edit Tafri Image Modal*/
    $(document).on(
        "click",
        'a[data-ajax-edit-tafri-image="true"]',
        function () {
            var title = $(this).data("title");
            var size = $(this).data("size") == "" ? "md" : $(this).data("size");
            var url = $(this).data("url");
            var data = {
                size: size,
                url: url,
            };
            $("#commanModel .modal-title").html(title);
            $("#commanModel .modal-dialog").addClass("modal-" + size);
            $.ajax({
                url: url,
                type: "GET",
                data: data,
                success: function (data) {
                    $("#commanModel .render-data").html(data.form);
                    $("#commanModel").modal("show");
                },
                error: function (data) {
                    data = data.responseJSON;
                },
            });
        }
    );

    /* Submit Tafri Image Edit Form */
    $(document)
        .off("submit", "#tafriImageEditForm")
        .on("submit", "#tafriImageEditForm", function (e) {
            e.preventDefault();
            var form = $(this);
            var submitButton = form.find('button[type="submit"]');
            $(".is-invalid").removeClass("is-invalid");
            $(".invalid-feedback").remove();
            submitButton
                .prop("disabled", true)
                .html(
                    '<span class="spinner-border spinner-border-sm"></span> Updating...'
                );
            $.ajax({
                url: form.attr("action"),
                type: "POST",
                data: new FormData(this),
                processData: false,
                contentType: false,
                success: function (response) {
                    submitButton.prop("disabled", false).html("Update");
                    if (response.status === true) {
                        $("#commanModel").modal("hide");
                        $(".display-tafri-image-list-html").html(response.html);
                        feather.replace();
                        Toastify({
                            text: response.message,
                            duration: 5000,
                            gravity: "top",
                            position: "right",
                            className: "bg-success",
                            close: true,
                        }).showToast();
                    }
                },
                error: function (xhr) {
                    submitButton.prop("disabled", false).html("Update");
                    if (xhr.status === 422) {
                        $.each(xhr.responseJSON.errors, function (key, value) {
                            $("#" + key)
                                .addClass("is-invalid")
                                .after(
                                    '<div class="invalid-feedback">' +
                                        value[0] +
                                        "</div>"
                                );
                        });
                    }
                },
            });
        });

    /* Delete Tafri Image Confirmation */
    $(document).on("click", ".show_confirm_tafri_image_delete", function (e) {
        e.preventDefault();
        var form = $(this).closest("form");
        var imageName = $(this).data("name") || "this image";
        Swal.fire({
            title: `Delete ${imageName}?`,
            text: "This action will permanently delete the image. This cannot be undone!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, delete it!",
            cancelButtonText: "Cancel",
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            reverseButtons: true,
            showLoaderOnConfirm: true,

            preConfirm: () => {
                return $.ajax({
                    url: form.attr("action"),
                    type: "POST",
                    data: {
                        _method: "DELETE",
                        _token: form.find('input[name="_token"]').val(),
                    },
                    dataType: "json",
                }).catch((error) => {
                    Swal.showValidationMessage(
                        error.responseJSON?.message || "Request failed"
                    );
                });
            },

            allowOutsideClick: () => !Swal.isLoading(),
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                var response = result.value;

                if (response.status === true) {
                    $(".display-tafri-image-list-html").html(response.html);
                    feather.replace();
                    Toastify({
                        text: response.message,
                        duration: 5000,
                        gravity: "top",
                        position: "right",
                        className: "bg-success",
                        close: true,
                    }).showToast();
                } else {
                    Toastify({
                        text: response.message || "Failed to delete image",
                        duration: 5000,
                        gravity: "top",
                        position: "right",
                        className: "bg-danger",
                        close: true,
                    }).showToast();
                }
            }
        });
    });

    /* Order Up/Down for Tafri Images */
    $(document).on(
        "submit",
        'form[action*="order-up"], form[action*="order-down"]',
        function (e) {
            e.preventDefault();
            var form = $(this);
            var submitButton = form.find('button[type="submit"]');
            var originalText = submitButton.html();

            submitButton
                .prop("disabled", true)
                .html('<span class="spinner-border spinner-border-sm"></span>');

            $.ajax({
                url: form.attr("action"),
                type: "POST",
                data: {
                    _token: form.find('input[name="_token"]').val(),
                },
                success: function (response) {                    
                    submitButton.prop("disabled", false).html(originalText);
                    location.reload();
                },
                error: function (xhr) {
                    submitButton.prop("disabled", false).html(originalText);
                    Toastify({
                        text: "Failed to update order. Please try again.",
                        duration: 5000,
                        gravity: "top",
                        position: "right",
                        className: "bg-danger",
                        close: true,
                    }).showToast();
                },
            });
        }
    );

    /* Image preview for multiple files in add form */
    $(document).on("change", "#tafri_image", function () {
        var files = $(this)[0].files;
        var previewContainer = $("#image-preview-container");

        if (!previewContainer.length) {
            $(this).after(
                '<div id="image-preview-container" class="mt-2"></div>'
            );
            previewContainer = $("#image-preview-container");
        }

        previewContainer.empty();

        for (var i = 0; i < files.length; i++) {
            var file = files[i];
            var reader = new FileReader();

            reader.onload = (function (file) {
                return function (e) {
                    previewContainer.append(
                        '<div class="image-preview-item d-inline-block m-1 text-center">' +
                            '<img src="' +
                            e.target.result +
                            '" class="img-thumbnail" width="80" height="80" alt="Preview" style="object-fit: cover;">' +
                            '<div class="small text-truncate" style="max-width: 80px;">' +
                            file.name +
                            "</div>" +
                            '<div class="small text-muted">' +
                            formatBytes(file.size) +
                            "</div>" +
                            "</div>"
                    );
                };
            })(file);

            reader.readAsDataURL(file);
        }
    });
    /* Format bytes helper function */
    function formatBytes(bytes, decimals = 2) {
        if (bytes === 0) return "0 Bytes";
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ["Bytes", "KB", "MB", "GB"];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return (
            parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + " " + sizes[i]
        );
    }
});
