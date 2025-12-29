$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});
var site_url = $('meta[name="base-url"]').attr("content");
$(document).ready(function () {
    $(document).on(
        "click",
        'a[data-ajax-testimonials-add-popup="true"]',
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
                    if (typeof flatpickr !== "undefined") {
                        setTimeout(function () {
                            let el =
                                document.getElementById("basic-datepicker");
                            if (el && !el._flatpickr) {
                                flatpickr(el, {
                                    dateFormat: "Y-m-d",
                                });
                            }
                        }, 200);
                    } else {
                        console.warn("Flatpickr library not loaded");
                    }
                },
                error: function (data) {
                    data = data.responseJSON;
                },
            });
        }
    );

    /* Submit Testimonials Add Form */
    $(document)
        .off("submit", "#testimonialsAddForm")
        .on("submit", "#testimonialsAddForm", function (event) {
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
                        $(".display-gallery-list-html").html(response.html);
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

    /* Testimonials Edit Modal Popup */
    $(document).on(
        "click",
        'a[data-ajax-edit-testimonial="true"]',
        function () {
            var title = $(this).data("title");
            var size = $(this).data("size") == "" ? "md" : $(this).data("size");
            var url = $(this).data("url");
            var data_action = $(this).data("action");
            var data = {
                size: size,
                url: url,
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
                    if (typeof flatpickr !== "undefined") {
                        setTimeout(function () {
                            let el = document.getElementById(
                                "basic-datepicker-edit"
                            );
                            if (el && !el._flatpickr) {
                                flatpickr(el, {
                                    dateFormat: "Y-m-d",
                                });
                            }
                        }, 200);
                    } else {
                        console.warn("Flatpickr library not loaded");
                    }
                },
                error: function (data) {
                    data = data.responseJSON;
                },
            });
        }
    );

    /* Submit Testimonials Edit Form */
    $(document)
        .off("submit", "#testimonialsEditForm")
        .on("submit", "#testimonialsEditForm", function (e) {
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
                        $(".display-gallery-list-html").html(response.html);
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

    /* Delete Testimonial Confirmation*/
    $(document).on("click", ".show_confirm_delete_testimonial", function (e) {
        e.preventDefault();
        var form = $(this).closest("form");
        var testimonialName = $(this).data("name") || "this testimonial";
        Swal.fire({
            title: `Delete ${testimonialName}?`,
            text: "This action will permanently delete the testimonial. This cannot be undone!",
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
                    $(".display-gallery-list-html").html(response.html);
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
                        text:
                            response.message || "Failed to delete testimonial",
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
});
