<?php

namespace App\Repositories;

use App\Data\TaskFilters;
use App\Models\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TaskRepository
{
    public function paginate(TaskFilters $filters, int $perPage = 15): LengthAwarePaginator
    {
        return Task::with('user')
            ->orderBy('due_date')
            ->withFilters($filters)
            ->paginate($perPage)
            ->withQueryString();
    }
}
