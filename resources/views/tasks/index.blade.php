@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Task Dashboard</h4>
</div>

<form id="filter-form" method="GET" action="{{ route('tasks.index') }}" class="card card-body mb-4">
    <div class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label small fw-semibold">Status</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">All statuses</option>
                <option value="pending"       {{ request('status') === 'pending'       ? 'selected' : '' }}>Pending</option>
                <option value="completed"     {{ request('status') === 'completed'     ? 'selected' : '' }}>Completed</option>
                <option value="non_compliant" {{ request('status') === 'non_compliant' ? 'selected' : '' }}>Non-Compliant</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold">Assigned To</label>
            <select name="user_id" class="form-select form-select-sm">
                <option value="">All users</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold">Due</label>
            <select name="due_filter" class="form-select form-select-sm">
                <option value="">All dates</option>
                <option value="today"   {{ request('due_filter') === 'today'   ? 'selected' : '' }}>Due Today</option>
                <option value="overdue" {{ request('due_filter') === 'overdue' ? 'selected' : '' }}>Overdue</option>
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
            <a href="#" id="btn-reset-filter" class="btn btn-outline-secondary btn-sm w-100">Reset</a>
        </div>
    </div>
</form>

<div id="task-table-wrapper">
    @include('tasks._table')
</div>

<div class="modal fade" id="viewTaskModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="view-task-title"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="view-task-body">
                <div class="text-center py-4">
                    <div class="spinner-border text-secondary" role="status"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-1"></i> Edit Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="modal-alert" class="alert d-none"></div>
                <form id="edit-task-form">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="edit-title" class="form-control">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" id="edit-description" rows="3" class="form-control"></textarea>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Due Date <span class="text-danger">*</span></label>
                            <input type="date" name="due_date" id="edit-due-date" class="form-control">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Priority <span class="text-danger">*</span></label>
                            <select name="priority" id="edit-priority" class="form-select">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Assign To <span class="text-danger">*</span></label>
                            <select name="user_id" id="edit-assigned-user" class="form-select">
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                            <select name="status" id="edit-status" class="form-select">
                                <option value="pending">Pending</option>
                                <option value="completed">Completed</option>
                                <option value="non_compliant">Non-Compliant</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div id="edit-corrective-action-group" class="mb-3 d-none">
                        <label class="form-label fw-semibold">Corrective Action Notes <span class="text-danger">*</span></label>
                        <textarea name="corrective_action" id="edit-corrective-action" rows="3" class="form-control"
                            placeholder="Describe the corrective action taken or planned..."></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btn-save-status">
                    <span class="btn-text">Save Changes</span>
                    <span class="btn-spinner spinner-border spinner-border-sm d-none"></span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
