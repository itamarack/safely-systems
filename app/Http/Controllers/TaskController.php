<?php

namespace App\Http\Controllers;

use App\Data\TaskFilters;
use App\Events\TaskUpdated;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Requests\UpdateTaskStatusRequest;
use App\Models\Task;
use App\Models\User;
use App\Repositories\TaskRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(private readonly TaskRepository $tasks) {}

    public function index(Request $request): View
    {
        return view('tasks.index', [
            'tasks' => $this->tasks->paginate(TaskFilters::fromRequest($request)),
            'users' => User::orderBy('name')->get(),
        ]);
    }

    public function table(Request $request): View
    {
        return view('tasks._table', [
            'tasks' => $this->tasks->paginate(TaskFilters::fromRequest($request)),
        ]);
    }

    public function store(StoreTaskRequest $request): RedirectResponse
    {
        $task = Task::create($request->validated());

        return redirect()->route('tasks.index')
            ->with('success', "Task \"{$task->title}\" created.");
    }

    public function show(Task $task): View
    {
        $task->load('user', 'activities.causer');

        return view('tasks._show', compact('task'));
    }

    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse
    {
        $task->update($request->validated());

        TaskUpdated::dispatch($task);

        return back()->with('success', 'Task updated.');
    }

    public function updateStatus(UpdateTaskStatusRequest $request, Task $task): RedirectResponse
    {
        $task->update($request->validated());

        TaskUpdated::dispatch($task);

        return back()->with('success', 'Task status updated.');
    }
}
