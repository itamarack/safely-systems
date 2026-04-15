<div class="row g-3 mb-3">
    <div class="col-sm-4">
        <div class="small text-muted">Status</div>
        @include('tasks._status_badge', ['status' => $task->status])
    </div>
    <div class="col-sm-4">
        <div class="small text-muted">Priority</div>
        <span class="badge {{ $task->priority->badgeClass() }}">{{ $task->priority->label() }}</span>
    </div>
    <div class="col-sm-4">
        <div class="small text-muted">Due Date</div>
        <span class="{{ $task->isOverdue() ? 'text-danger fw-semibold' : '' }}">
            {{ $task->due_date->format('d M Y') }}
            @if($task->isOverdue()) <span class="badge bg-danger">Overdue</span> @endif
            @if($task->isDueToday()) <span class="badge bg-warning text-dark">Today</span> @endif
        </span>
    </div>
    <div class="col-sm-4">
        <div class="small text-muted">Assigned To</div>
        {{ $task->user->name }}
    </div>
    <div class="col-sm-4">
        <div class="small text-muted">Created</div>
        {{ $task->created_at->format('d M Y') }}
    </div>
</div>

@if($task->description)
    <p class="text-muted">{{ $task->description }}</p>
@endif

@if($task->corrective_action)
    <div class="alert alert-danger">
        <strong><i class="bi bi-exclamation-triangle me-1"></i> Corrective Action:</strong>
        <p class="mb-0 mt-1">{{ $task->corrective_action }}</p>
    </div>
@endif

<hr>
<h6 class="fw-semibold"><i class="bi bi-clock-history me-1"></i> Activity Log</h6>
<ul class="list-group list-group-flush">
    @forelse($task->activities->sortByDesc('created_at') as $log)
        <li class="list-group-item px-0">
            <div class="d-flex justify-content-between">
                <span>
                    <strong>{{ $log->causer?->name ?? 'System' }}</strong>
                    {{ $log->description }}
                </span>
                <small class="text-muted text-nowrap ms-2">{{ $log->created_at->diffForHumans() }}</small>
            </div>
            @if($log->attribute_changes && $log->attribute_changes->get('attributes'))
                <div class="mt-1">
                    @foreach($log->attribute_changes->get('attributes') as $field => $value)
                        <span class="badge bg-light text-dark border me-1">
                            {{ $field }}: {{ is_array($value) ? json_encode($value) : $value }}
                        </span>
                    @endforeach
                </div>
            @endif
        </li>
    @empty
        <li class="list-group-item px-0 text-muted">No activity recorded yet.</li>
    @endforelse
</ul>
