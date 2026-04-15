import $ from 'jquery';
import * as bootstrap from 'bootstrap';

window.$ = $;
window.jQuery = $;
window.bootstrap = bootstrap;

$(function () {
    const routes = window.AppRoutes || {};
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    let currentTaskId = null;

    const modal = {
        show(id) {
            const element = $('#' + id).get(0);
            if (element) bootstrap.Modal.getOrCreateInstance(element).show();
        },

        hide(id) {
            const element = $('#' + id).get(0);
            if (element) bootstrap.Modal.getOrCreateInstance(element).hide();
        },
    };

    const api = {
        request(method, url, data = {}) {
            return $.ajax({
                url,
                method,
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                data: { _token: csrfToken, ...data },
            }).fail((xhr) => {
                console.error(xhr);
            });
        },

        createTask(data) {
            return this.request('POST', routes.tasksStore, data);
        },

        updateTask(id, data) {
            return this.request('POST', routes.tasksUpdate.replace(':id', id), { _method: 'PUT', ...data });
        },

        updateStatus(id, status) {
            return this.request('PATCH', routes.tasksStatus.replace(':id', id), { status });
        },
    };

    const ui = {
        toast(message) {
            $('#toast-message').text(message);
            new bootstrap.Toast($('#app-toast').get(0), { delay: 3500 }).show();
        },

        setLoading(button, isLoading, textDefault) {
            const textEl = button.find('.btn-text');
            const spinner = button.find('.btn-spinner');
            button.prop('disabled', isLoading);
            spinner.toggleClass('d-none', !isLoading);
            if (isLoading) {
                textEl.data('original', textEl.text());
                textEl.text('Please wait...');
            } else {
                textEl.text(textDefault || textEl.data('original'));
            }
        },

        clearValidation(form) {
            form.find('.is-invalid').removeClass('is-invalid');
            form.find('.invalid-feedback').text('');
        },

        showValidationErrors(xhr, form, alertBox = null) {
            if (xhr.status === 422) {
                $.each(xhr.responseJSON.errors, (field, messages) => {
                    const input = form.find(`[name="${field}"]`);
                    input.addClass('is-invalid');
                    input.siblings('.invalid-feedback').text(messages[0]);
                });
            } else if (alertBox) {
                alertBox.removeClass('d-none').addClass('alert-danger').text('Something went wrong. Please try again.');
            }
        },
    };

    const table = {
        reload(url = null) {
            const params = new URLSearchParams(window.location.search);
            let fetchUrl = routes.tasksTable + '?' + params.toString();

            if (url) {
                const parsed = new URL(url, window.location.origin);
                fetchUrl = routes.tasksTable + '?' + parsed.searchParams.toString();
                window.history.pushState({}, '', parsed.pathname.replace('/table', '') + '?' + parsed.searchParams.toString());
            }

            $.get(fetchUrl, (html) => {
                $('#task-table-wrapper').html(html);
            });
        },
    };

    function getFormData(form) {
        return Object.fromEntries(new FormData(form.get(0)).entries());
    }

    function resetForm(form, alertBox = null) {
        form.get(0).reset();
        ui.clearValidation(form);
        alertBox?.addClass('d-none').text('');
    }

    $(document).on('input change', 'form input, form textarea, form select', function () {
        $(this).removeClass('is-invalid');
        $(this).siblings('.invalid-feedback').text('');
    });

    $(document).on('click', '#task-table-wrapper .pagination a', function (e) {
        e.preventDefault();
        table.reload($(this).attr('href'));
    });

    $('#filter-form').on('submit', function (e) {
        e.preventDefault();
        const params = new URLSearchParams($(this).serialize());
        window.history.pushState({}, '', '?' + params.toString());
        table.reload(routes.tasksTable + '?' + params.toString());
    });

    $('#btn-reset-filter').on('click', function (e) {
        e.preventDefault();
        $('#filter-form').get(0).reset();
        window.history.pushState({}, '', window.location.pathname);
        table.reload();
    });

    $('#btn-create-task').on('click', function () {
        const button = $(this);
        const form = $('#create-task-form');
        ui.setLoading(button, true);
        ui.clearValidation(form);

        api.createTask(getFormData(form))
            .done((response) => {
                modal.hide('createTaskModal');
                table.reload();
                ui.toast(response.message);
            })
            .fail((xhr) => {
                ui.showValidationErrors(xhr, form, $('#create-alert'));
            })
            .always(() => {
                ui.setLoading(button, false, 'Create Task');
            });
    });

    $('#createTaskModal').on('hidden.bs.modal', function () {
        resetForm($('#create-task-form'), $('#create-alert'));
    });

    $(document).on('click', '.btn-quick-status', function () {
        const { taskId, status } = $(this).data();

        if (status === 'non_compliant') {
            $(this).closest('tr').find('.btn-update-status').click();
            $('#statusModal').one('shown.bs.modal', function () {
                $('#edit-status').val('non_compliant').trigger('change');
            });
            return;
        }

        api.updateStatus(taskId, status)
            .done((response) => {
                table.reload();
                ui.toast(response.message);
            })
            .fail(() => ui.toast('Something went wrong.'));
    });

    $(document).on('click', '.btn-update-status', function () {
        const data = $(this).data();
        currentTaskId = data.taskId;

        $('#edit-task-form').find('[name]').each(function () {
            const name = $(this).attr('name');
            $(this).val(data[`task${name.charAt(0).toUpperCase() + name.slice(1)}`] || '');
        });

        $('#edit-status').val(data.currentStatus).trigger('change');
        ui.clearValidation($('#edit-task-form'));
        modal.show('statusModal');
    });

    $('#edit-status').on('change', function () {
        const isNonCompliant = $(this).val() === 'non_compliant';
        $('#edit-corrective-action-group').toggleClass('d-none', !isNonCompliant);
        if (!isNonCompliant) {
            $('#edit-corrective-action').val('').removeClass('is-invalid');
        }
    });

    $('#btn-save-status').on('click', function () {
        const button = $(this);
        const form = $('#edit-task-form');
        ui.setLoading(button, true);
        ui.clearValidation(form);

        api.updateTask(currentTaskId, getFormData(form))
            .done((response) => {
                modal.hide('statusModal');
                table.reload();
                ui.toast(response.message);
            })
            .fail((xhr) => {
                ui.showValidationErrors(xhr, form, $('#modal-alert'));
            })
            .always(() => {
                ui.setLoading(button, false, 'Save Changes');
            });
    });

    $(document).on('click', '.btn-view-task', function () {
        const { taskId, taskTitle } = $(this).data();
        $('#view-task-title').text(taskTitle);
        $('#view-task-body').html(`<div class="text-center py-4"><div class="spinner-border text-secondary"></div></div>`);
        modal.show('viewTaskModal');

        $.get(routes.tasksShow.replace(':id', taskId))
            .done((html) => $('#view-task-body').html(html))
            .fail(() => {
                $('#view-task-body').html('<div class="text-danger text-center py-4">Failed to load task.</div>');
            });
    });
});
