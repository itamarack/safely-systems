<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Title</th>
                    <th>Assigned To</th>
                    <th>Due Date</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tasks as $task)
                    @php
                        $rowClass = '';
                        if ($task->isOverdue()) $rowClass = 'table-overdue';
                        elseif ($task->isDueToday()) $rowClass = 'table-due-today';
                    @endphp
                    <tr class="{{ $rowClass }}">
                        <td>
                            <a href="#" class="fw-semibold text-decoration-none text-dark btn-view-task"
                                data-task-id="{{ $task->id }}"
                                data-task-title="{{ $task->title }}">
                                {{ $task->title }}
                            </a>
                            @if($task->isOverdue())
                                <span class="badge bg-danger ms-1">Overdue</span>
                            @elseif($task->isDueToday())
                                <span class="badge bg-warning text-dark ms-1">Due Today</span>
                            @endif
                        </td>
                        <td>{{ $task->user->name }}</td>
                        <td>{{ $task->due_date->format('d M Y') }}</td>
                        <td>
                            <span class="badge {{ $task->priority->badgeClass() }}">
                                {{ $task->priority->label() }}
                            </span>
                        </td>
                        <td>@include('tasks._status_badge', ['status' => $task->status])</td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm" role="group">
                                <button class="btn btn-outline-dark btn-view-task"
                                    data-task-id="{{ $task->id }}"
                                    data-task-title="{{ $task->title }}"
                                    title="View details">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-outline-primary btn-update-status"
                                    data-task-id="{{ $task->id }}"
                                    data-task-title="{{ $task->title }}"
                                    data-task-description="{{ $task->description }}"
                                    data-task-due_date="{{ $task->due_date->format('Y-m-d') }}"
                                    data-task-priority="{{ $task->priority->value }}"
                                    data-task-user_id="{{ $task->user_id }}"
                                    data-task-corrective_action="{{ $task->corrective_action }}"
                                    data-current-status="{{ $task->status->value }}"
                                    title="Edit task">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                @if($task->status !== \App\Enums\TaskStatus::Completed)
                                <button class="btn btn-outline-success btn-quick-status"
                                    data-task-id="{{ $task->id }}"
                                    data-status="completed"
                                    title="Mark as completed">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                                @endif
                                @if($task->status !== \App\Enums\TaskStatus::NonCompliant)
                                <button class="btn btn-outline-danger btn-quick-status"
                                    data-task-id="{{ $task->id }}"
                                    data-status="non_compliant"
                                    title="Mark as non-compliant">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                                @endif
                                @if($task->status !== \App\Enums\TaskStatus::Pending)
                                <button class="btn btn-outline-warning btn-quick-status"
                                    data-task-id="{{ $task->id }}"
                                    data-status="pending"
                                    title="Reset to pending">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No tasks found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $tasks->links() }}</div>
