$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});

var site_url = $('meta[name="base-url"]').attr("content");
$(document).ready(function () {
    $(document).on("click", 'a[data-ajax-banner-add="true"]', function () {
        var title = $(this).data("title");
        var size = $(this).data("size") == "" ? "md" : $(this).data("size");
        var action = $(this).data("action") == "" ? "" : $(this).data("action");
        var url = $(this).data("url");
        var data = {
            size: size,
            url: url,
            action: action,
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
        .off("submit", "#bannerForm")
        .on("submit", "#bannerForm", function (event) {
            event.preventDefault();
            var form = $(this);
            var submitButton = form.find('button[type="submit"]');
            $(".form-control").removeClass("is-invalid");
            $(".invalid-feedback").remove();
            var formData = new FormData(this);
            $("#uploadProgressWrapper").show();
            $("#uploadProgress").css("width", "0%").text("0%");
            var xhr = null;
            $.ajax({
                xhr: function () {
                    xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener(
                        "progress",
                        function (evt) {
                            if (evt.lengthComputable) {
                                var percentComplete = Math.round(
                                    (evt.loaded / evt.total) * 100
                                );
                                $("#uploadProgress")
                                    .css("width", percentComplete + "%")
                                    .text(percentComplete + "%");
                                if (percentComplete < 100) {
                                    submitButton.html(
                                        '<span class="spinner-border spinner-border-sm" role="status"></span> Uploading ' +
                                            percentComplete +
                                            "%"
                                    );
                                }
                            }
                        },
                        false
                    );

                    xhr.addEventListener("loadstart", function () {
                        console.log("Upload started");
                    });

                    xhr.addEventListener("error", function () {
                        console.log("Upload error");
                    });

                    xhr.addEventListener("abort", function () {
                        console.log("Upload aborted");
                    });

                    return xhr;
                },
                url: form.attr("action"),
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                timeout: 300000,
                beforeSend: function () {
                    submitButton
                        .prop("disabled", true)
                        .html(
                            '<span class="spinner-border spinner-border-sm" role="status"></span> Starting upload...'
                        );
                    if (!$("#cancelUploadBtn").length) {
                        submitButton.after(
                            '<button type="button" id="cancelUploadBtn" class="btn btn-danger ms-2">Cancel</button>'
                        );
                        $("#cancelUploadBtn").click(function () {
                            if (xhr) {
                                xhr.abort();
                                resetFormState();
                                Toastify({
                                    text: "Upload cancelled",
                                    duration: 3000,
                                    gravity: "top",
                                    position: "right",
                                    className: "bg-warning",
                                    close: true,
                                }).showToast();
                            }
                        });
                    }
                },
                success: function (response) {
                    if (response && response.status) {
                        if (response.status === "success") {
                            $("#uploadProgress")
                                .css("width", "100%")
                                .text("Upload Complete");

                            if (response.videoListData) {
                                $("#banner-list").html(response.videoListData);
                            }
                            feather.replace();
                            form[0].reset();
                            $("#commanModel").modal("hide");
                            Toastify({
                                text:
                                    response.message ||
                                    "Video uploaded successfully!",
                                duration: 5000,
                                gravity: "top",
                                position: "right",
                                className: "bg-success",
                                close: true,
                            }).showToast();
                        } else if (response.status === "error") {
                            handleUploadError(
                                response.message || "Upload failed"
                            );
                        }
                    } else {
                        handleUploadError("Invalid response from server");
                    }

                    resetFormState();
                },
                error: function (xhr, status, error) {
                    var errorMessage = "An error occurred during upload";

                    if (status === "timeout") {
                        errorMessage = "Upload timed out. Please try again.";
                    } else if (status === "abort") {
                        errorMessage = "Upload was cancelled.";
                        console.log("Upload aborted by user");
                        return;
                    } else if (xhr.status === 413) {
                        errorMessage =
                            "File is too large. Please choose a smaller video.";
                    } else if (xhr.status === 422) {
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            displayValidationErrors(xhr.responseJSON.errors);
                            errorMessage =
                                xhr.responseJSON.message ||
                                "Please fix the validation errors";
                        }
                    } else if (xhr.status === 500) {
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else {
                            errorMessage =
                                "Server error. Please try again later.";
                        }
                    } else if (xhr.status === 0) {
                        errorMessage =
                            "Network connection lost. Please check your internet connection.";
                    }
                    handleUploadError(errorMessage);
                },
                complete: function () {
                    $("#cancelUploadBtn").remove();
                },
            });

            function handleUploadError(message) {
                $("#uploadProgress").css("width", "0%").text("Upload Failed");
                $("#uploadProgress").removeClass("progress-bar-animated");
                Toastify({
                    text: message,
                    duration: 8000,
                    gravity: "top",
                    position: "right",
                    className: "bg-danger",
                    close: true,
                    stopOnFocus: true,
                }).showToast();

                resetFormState();
            }

            function displayValidationErrors(errors) {
                $.each(errors, function (field, messages) {
                    var input = form.find('[name="' + field + '"]');
                    var feedbackElement =
                        input
                            .closest(".form-group")
                            .find(".invalid-feedback") ||
                        input.next(".invalid-feedback");

                    input.addClass("is-invalid");
                    if (feedbackElement.length === 0) {
                        input.after(
                            '<div class="invalid-feedback">' +
                                messages[0] +
                                "</div>"
                        );
                    } else {
                        feedbackElement.text(messages[0]);
                    }
                });
            }

            function resetFormState() {
                submitButton.prop("disabled", false).html("Submit");
                setTimeout(function () {
                    $("#uploadProgressWrapper").hide();
                    $("#uploadProgress").addClass("progress-bar-animated");
                }, 3000);
            }
        });

    $(document).on("click", 'a[data-ajax-edit-video="true"]', function () {
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
        .off("submit", "#bannerEditForm")
        .on("submit", "#bannerEditForm", function (event) {
            event.preventDefault();
            var form = $(this);
            var submitButton = form.find('button[type="submit"]');
            $(".form-control").removeClass("is-invalid");
            $(".invalid-feedback").remove();
            var formData = new FormData(this);
            formData.append("_method", "PUT");
            $("#uploadProgressWrapper").show();
            $("#uploadProgress").css("width", "0%").text("0%");
            var xhr = null;
            $.ajax({
                xhr: function () {
                    xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener(
                        "progress",
                        function (evt) {
                            if (evt.lengthComputable) {
                                var percentComplete = Math.round(
                                    (evt.loaded / evt.total) * 100
                                );
                                $("#uploadProgress")
                                    .css("width", percentComplete + "%")
                                    .text(percentComplete + "%");
                                if (percentComplete < 100) {
                                    submitButton.html(
                                        '<span class="spinner-border spinner-border-sm" role="status"></span> Uploading ' +
                                            percentComplete +
                                            "%"
                                    );
                                }
                            }
                        },
                        false
                    );

                    xhr.addEventListener("loadstart", function () {
                        console.log("Upload started");
                    });

                    xhr.addEventListener("error", function () {
                        console.log("Upload error");
                    });

                    xhr.addEventListener("abort", function () {
                        console.log("Upload aborted");
                    });

                    return xhr;
                },
                url: form.attr("action"),
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                timeout: 300000,
                beforeSend: function () {
                    submitButton
                        .prop("disabled", true)
                        .html(
                            '<span class="spinner-border spinner-border-sm" role="status"></span> Starting upload...'
                        );
                    if (!$("#cancelUploadBtn").length) {
                        submitButton.after(
                            '<button type="button" id="cancelUploadBtn" class="btn btn-danger ms-2">Cancel</button>'
                        );
                        $("#cancelUploadBtn").click(function () {
                            if (xhr) {
                                xhr.abort();
                                resetFormState();
                                Toastify({
                                    text: "Upload cancelled",
                                    duration: 3000,
                                    gravity: "top",
                                    position: "right",
                                    className: "bg-warning",
                                    close: true,
                                }).showToast();
                            }
                        });
                    }
                },
                success: function (response) {
                    if (response && response.status) {
                        if (response.status === "success") {
                            $("#uploadProgress")
                                .css("width", "100%")
                                .text("Update Complete");

                            if (response.videoListData) {
                                $("#banner-list").html(response.videoListData);
                            }
                            feather.replace();
                            form[0].reset();
                            $("#commanModel").modal("hide");
                            Toastify({
                                text:
                                    response.message ||
                                    "Banner updated successfully!",
                                duration: 5000,
                                gravity: "top",
                                position: "right",
                                className: "bg-success",
                                close: true,
                            }).showToast();
                        } else if (response.status === "error") {
                            handleUploadError(
                                response.message || "Update failed"
                            );
                        }
                    } else {
                        handleUploadError("Invalid response from server");
                    }

                    resetFormState();
                },
                error: function (xhr, status, error) {
                    var errorMessage = "An error occurred during update";

                    if (status === "timeout") {
                        errorMessage = "Upload timed out. Please try again.";
                    } else if (status === "abort") {
                        errorMessage = "Update was cancelled.";
                        console.log("Upload aborted by user");
                        return;
                    } else if (xhr.status === 413) {
                        errorMessage =
                            "File is too large. Please choose a smaller video.";
                    } else if (xhr.status === 422) {
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            displayValidationErrors(xhr.responseJSON.errors);
                            errorMessage =
                                xhr.responseJSON.message ||
                                "Please fix the validation errors";
                        }
                    } else if (xhr.status === 500) {
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else {
                            errorMessage =
                                "Server error. Please try again later.";
                        }
                    } else if (xhr.status === 0) {
                        errorMessage =
                            "Network connection lost. Please check your internet connection.";
                    }
                    handleUploadError(errorMessage);
                },
                complete: function () {
                    $("#cancelUploadBtn").remove();
                },
            });

            function handleUploadError(message) {
                $("#uploadProgress").css("width", "0%").text("Update Failed");
                $("#uploadProgress").removeClass("progress-bar-animated");
                Toastify({
                    text: message,
                    duration: 8000,
                    gravity: "top",
                    position: "right",
                    className: "bg-danger",
                    close: true,
                    stopOnFocus: true,
                }).showToast();

                resetFormState();
            }

            function displayValidationErrors(errors) {
                $.each(errors, function (field, messages) {
                    var input = form.find('[name="' + field + '"]');
                    var feedbackElement =
                        input
                            .closest(".form-group")
                            .find(".invalid-feedback") ||
                        input.next(".invalid-feedback");

                    input.addClass("is-invalid");
                    if (feedbackElement.length === 0) {
                        input.after(
                            '<div class="invalid-feedback">' +
                                messages[0] +
                                "</div>"
                        );
                    } else {
                        feedbackElement.text(messages[0]);
                    }
                });
            }

            function resetFormState() {
                submitButton.prop("disabled", false).html("Update");
                setTimeout(function () {
                    $("#uploadProgressWrapper").hide();
                    $("#uploadProgress").addClass("progress-bar-animated");
                }, 3000);
            }
        });

    $(document).on("click", ".show_confirm", function (e) {
        e.preventDefault();
        var form = $(this).closest("form");
        var bannerName = $(this).data("name") || "banner";
        var bannerId = $(this).data("id") || "unknown";

        Swal.fire({
            title: `Delete ${bannerName}?`,
            text: "This action will permanently delete the banner video(s) from both the system and cloud storage. This cannot be undone!",
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
                        `Request failed: ${
                            error.responseJSON?.message ||
                            error.statusText ||
                            "Unknown error"
                        }`
                    );
                });
            },
            allowOutsideClick: () => !Swal.isLoading(),
        }).then((result) => {
            if (result.isConfirmed) {
                var response = result.value;
                if (response && response.status === "success") {
                    if (response.videoListData) {
                        $("#banner-list").html(response.videoListData);
                    }
                    feather.replace();
                    Toastify({
                        text:response.message || "Banner deleted successfully!",
                        duration: 5000,
                        gravity: "top",
                        position: "right",
                        className: "bg-success",
                        close: true,
                        stopOnFocus: true,
                    }).showToast();
                    if (
                        $(".empty-state").length &&
                        response.videoListData.trim() === ""
                    ) {
                        $(".display-video-list-html").html(`
                        <div class="text-center py-5">
                            <div class="empty-state-icon">
                                <i class="fas fa-video-slash fa-3x text-muted"></i>
                            </div>
                            <h4 class="mt-3">No Banners Found</h4>
                            <p class="text-muted">Click the "Add Banner" button to create your first banner.</p>
                        </div>
                    `);
                    }
                } else if (response && response.status === "error") {
                    Toastify({
                        text: response.message || "Failed to delete banner",
                        duration: 8000,
                        gravity: "top",
                        position: "right",
                        className: "bg-danger",
                        close: true,
                        stopOnFocus: true,
                    }).showToast();
                } else {
                    Toastify({
                        text: "Unexpected response from server",
                        duration: 8000,
                        gravity: "top",
                        position: "right",
                        className: "bg-warning",
                        close: true,
                        stopOnFocus: true,
                    }).showToast();
                }
            }
        });
    });
});
