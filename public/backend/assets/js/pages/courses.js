$(document).ready(function () {
    $('.add_more_additional').click(function () {
        let container = $('#additionalContentContainer');
        let count = container.children().length + 1;
        let uniqueId = 'ckeditor_' + Date.now();
        let newRow = `
        <tr class="paragraph-row">
            <td style="width: 50%">
                <span class="counter-badge">${count}</span>
                <label class="form-label">Courses Additional Title</label>
                <input type="text" name="courses_additional_title[]" class="form-control" placeholder="Enter Courses Additional Title" required>
            </td>                                    
            <td>
                <label class="form-label">Courses Additional Content</label>
                <textarea name="courses_additional_content[]" class="ckeditor4 form-control" placeholder="Enter detailed content here" id="${uniqueId}"></textarea>
                <div class="remove-btn-container">
                    <button type="button" class="btn btn-danger btn-sm remove-paragraph"><i class="fas fa-trash me-1"></i>Remove</button>
                </div>
            </td>
        </tr>`;
        container.append(newRow);
        CKEDITOR.replace(uniqueId, {
            removePlugins: 'exportpdf'
        });
        updateCounters();
    });
    $('.add_more_highlights').click(function () {
        let container = $('#highlightsContainer');
        let newRow = container.find('.paragraph-row').first().clone();
        newRow.find('input[type="text"]').val('');
        newRow.find('select').prop('selectedIndex', 0);
        newRow.find('.remove-paragraph').show();
        newRow.find('.remove-paragraph').click(function () {
            $(this).closest('.paragraph-row').remove();
        });
        container.append(newRow);
    });
    $(document).on('click', '.remove-paragraph', function () {
        if ($(this).closest('tbody').children().length > 1) {
            $(this).closest('.paragraph-row').remove();
            updateCounters();
        }
    });
    function updateCounters() {
        $('#additionalContentContainer .paragraph-row').each(function (index) {
            $(this).find('.counter-badge').text(index + 1);
        });

        $('#highlightsContainer .paragraph-row').each(function (index) {
            $(this).find('.counter-badge').text(index + 1);
        });
    }

    $('.show_confirm').click(function (event) {
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

    /**Add more eligibity */
    /*$('.add_more_eligibity').click(function () {
        let container = $('#eligibityContentContainer');
        let templateRow = container.find('.eligibity-row').first();
        if (templateRow.length === 0) {
            templateRow = container.find('.paragraph-row').first().clone();
            templateRow.removeClass('paragraph-row').addClass('eligibity-row');
        } else {
            templateRow = templateRow.clone();
        }
        templateRow.find('input[type="text"]').val('');
        templateRow.find('textarea').val('');
        templateRow.find('.remove-eligibity').show();
        templateRow.find('.remove-eligibity').off('click').on('click', function () {
            $(this).closest('.eligibity-row').remove();
        });
        container.append(templateRow);
    });
    $(document).on('click', '.remove-eligibity', function() {
        $(this).closest('tr').remove();
    });
    */
    $(document).off('submit', '#coursesFormAdd').on('submit', '#coursesFormAdd', function (event) {
        event.preventDefault();
        var form = $(this);
        var submitButton = form.find('button[type="submit"]');
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        submitButton.prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...'
        );
        var formData = new FormData(this);
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                submitButton.prop('disabled', false).html('Submit');
                if (response.status === 'success') {
                    Toastify({
                        text: response.message,
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        className: "bg-success",
                        close: true
                    }).showToast();
                    setTimeout(function () {
                        window.location.href = response.redirect_url;
                    }, 300);
                }
            },
            error: function (xhr) {
                submitButton.prop('disabled', false).html('Submit');
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    Toastify({
                        text: xhr.responseJSON.message,
                        duration: 10000,
                        gravity: "top",
                        position: "right",
                        className: "bg-danger",
                        close: true
                    }).showToast();
                }

                var errors = xhr.responseJSON.errors;
                if (errors) {
                    let firstErrorField = null;
                    $.each(errors, function (key, value) {
                        var inputField = $('[name="' + key + '"]'); 
                        inputField.addClass('is-invalid');
                        inputField.after('<div class="invalid-feedback">' + value[0] + '</div>');
                        if (!firstErrorField) {
                            firstErrorField = inputField;
                        }
                    });
                    if (firstErrorField) {
                        firstErrorField.focus();
                    }
                }
            }
        });
    });

    $(document).off('submit', '#coursesFormEdit').on('submit', '#coursesFormEdit', function (event) {
        event.preventDefault();
        var form = $(this);
        var submitButton = form.find('button[type="submit"]');
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        submitButton.prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...'
        );
        var formData = new FormData(this);
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                submitButton.prop('disabled', false).html('Submit');
                if (response.status === 'success') {
                    Toastify({
                        text: response.message,
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        className: "bg-success",
                        close: true
                    }).showToast();
                    setTimeout(function () {
                        window.location.href = response.redirect_url;
                    }, 300);
                }
            },
            error: function (xhr) {
                submitButton.prop('disabled', false).html('Submit');
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    Toastify({
                        text: xhr.responseJSON.message,
                        duration: 10000,
                        gravity: "top",
                        position: "right",
                        className: "bg-danger",
                        close: true
                    }).showToast();
                }

                var errors = xhr.responseJSON.errors;
                if (errors) {
                    let firstErrorField = null;
                    $.each(errors, function (key, value) {
                        var inputField = $('[name="' + key + '"]'); 
                        inputField.addClass('is-invalid');
                        inputField.after('<div class="invalid-feedback">' + value[0] + '</div>');
                        if (!firstErrorField) {
                            firstErrorField = inputField;
                        }
                    });
                    if (firstErrorField) {
                        firstErrorField.focus();
                    }
                }
            }
        });
    });

});