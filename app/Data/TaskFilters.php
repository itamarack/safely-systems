<?php

namespace App\Data;

use App\Enums\DueFilter;
use App\Enums\TaskStatus;
use App\Models\User;
use Illuminate\Http\Request;

final class TaskFilters
{
    public function __construct(
        public readonly ?TaskStatus $status,
        public readonly ?User       $user,
        public readonly ?DueFilter  $dueFilter,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            status:    TaskStatus::tryFrom($request->input('status', '')),
            user:      User::find($request->integer('user_id') ?: null),
            dueFilter: DueFilter::tryFrom($request->input('due_filter', '')),
        );
    }
}
