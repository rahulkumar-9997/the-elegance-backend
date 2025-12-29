$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});

var site_url = $('meta[name="base-url"]').attr("content");
$(document).ready(function () {
    $(document).on("click", 'a[data-ajax-add-images="true"]', function () {
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
    });

    $(document)
        .off("submit", "#banquetImagesAddForm")
        .on("submit", "#banquetImagesAddForm", function (event) {
            event.preventDefault();
            var form = $(this);
            var submitButton = form.find('button[type="submit"]');
            $(".form-control").removeClass("is-invalid");
            $(".invalid-feedback").remove();
            submitButton
                .prop("disabled", true)
                .html(
                    '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Uploading...'
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
                    submitButton.html("Upload Images");
                    if (response.status === true) {
                        form[0].reset();
                        $("#commanModel").modal("hide");
                        $(".display-banquets-list-html").html(response.html);
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
                    submitButton.html("Upload Images");
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
                    } else {
                        Toastify({
                            text:
                                xhr.responseJSON.message ||
                                "An error occurred. Please try again.",
                            duration: 5000,
                            gravity: "top",
                            position: "right",
                            className: "bg-danger",
                            close: true,
                        }).showToast();
                    }
                },
            });
        });

    // ============================================
    // DELETE BANQUET IMAGE CONFIRMATION
    // ============================================
    $(document).on("click", ".show_confirm_image_delete", function (e) {
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
                    $(".display-banquets-list-html").html(response.html);
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

    // ============================================
    // VIEW ALL IMAGES MODAL
    // ============================================
    $(document).on("click", 'a[data-ajax-view-images="true"]', function () {
        var title = $(this).data("title");
        var size = $(this).data("size") == "" ? "lg" : $(this).data("size");
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
                $("#commanModel .render-data").html(data.html);
                $("#commanModel").modal("show");
            },
            error: function (data) {
                data = data.responseJSON;
            },
        });
    });

    // ============================================
    // IMAGE PREVIEW FOR MULTIPLE FILES
    // ============================================
    $(document).on("change", "#banquets_image", function () {
        var files = $(this)[0].files;
        var previewContainer = $("#image-preview-container");
        if (!previewContainer.length) {
            $(this).after(
                '<div id="image-preview-container" class="mt-2"></div>'
            );
            previewContainer = $("#image-preview-container");
        }
        previewContainer.empty();
        if (files.length > 10) {
            previewContainer.append(
                '<div class="alert alert-warning">You can upload maximum 10 images. First 10 will be selected.</div>'
            );
        }
        for (var i = 0; i < Math.min(files.length, 10); i++) {
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
});

function formatBytes(bytes, decimals = 2) {
    if (bytes === 0) return "0 Bytes";
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ["Bytes", "KB", "MB", "GB"];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + " " + sizes[i];}
if (typeof feather !== "undefined") {
    feather.replace();
}
