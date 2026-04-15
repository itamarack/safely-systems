<?php

namespace App\Http\Controllers\Api;

use App\Data\TaskFilters;
use App\Events\TaskUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Requests\UpdateTaskStatusRequest;
use App\Http\Resources\TaskCollection;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Repositories\TaskRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TaskController extends Controller
{
    public function __construct(private readonly TaskRepository $tasks) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Tasks retrieved.',
            'data'    => new TaskCollection($this->tasks->paginate(TaskFilters::fromRequest($request))),
        ], Response::HTTP_OK);
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = Task::create($request->validated());

        return response()->json([
            'message' => 'Task created.',
            'data'    => new TaskResource($task->fresh()->load('user')),
        ], Response::HTTP_CREATED);
    }

    public function show(Task $task): JsonResponse
    {
        return response()->json([
            'message' => 'Task retrieved.',
            'data'    => new TaskResource($task->load('user')),
        ], Response::HTTP_OK);
    }

    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $task->update($request->validated());

        TaskUpdated::dispatch($task);

        return response()->json([
            'message' => 'Task updated.',
            'data'    => new TaskResource($task->fresh()->load('user')),
        ], Response::HTTP_OK);
    }

    public function updateStatus(UpdateTaskStatusRequest $request, Task $task): JsonResponse
    {
        $task->update($request->validated());

        TaskUpdated::dispatch($task);

        return response()->json([
            'message' => 'Task status updated.',
            'data'    => new TaskResource($task->fresh()->load('user')),
        ], Response::HTTP_OK);
    }
}
