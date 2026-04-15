<?php

namespace App\Models;

use App\Data\TaskFilters;
use App\Enums\DueFilter;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\HasActivity;
use Spatie\Activitylog\Support\LogOptions;

/** @property \Illuminate\Support\Carbon $due_date */
#[Fillable([
    'title',
    'description',
    'due_date',
    'user_id',
    'priority',
    'status',
    'corrective_action',
])]
class Task extends Model
{
    use HasFactory, HasActivity;

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'status'   => TaskStatus::class,
            'priority' => TaskPriority::class,
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'status', 'priority', 'user_id', 'corrective_action'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('task');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeWithFilters(Builder $query, TaskFilters $filters): Builder
    {
        return $query
            ->when($filters->status, fn (Builder $q) => $q->where('status', $filters->status))
            ->when($filters->user, fn (Builder $q) => $q->where('user_id', $filters->user->id))
            ->when($filters->dueFilter === DueFilter::Today, fn (Builder $q) => $q->whereDate('due_date', today()))
            ->when($filters->dueFilter === DueFilter::Overdue, fn (Builder $q) => $q->where('due_date', '<', today())->where('status', TaskStatus::Pending));
    }

    public function isNonCompliant(): bool
    {
        return $this->status === TaskStatus::NonCompliant;
    }

    public function isOverdue(): bool
    {
        return $this->status === TaskStatus::Pending && $this->due_date->isPast();
    }

    public function isDueToday(): bool
    {
        return $this->due_date->isToday();
    }
}
