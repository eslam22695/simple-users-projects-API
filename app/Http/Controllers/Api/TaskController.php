<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\TaskFilterRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskService $taskService,
    ) {}

    public function index(TaskFilterRequest $request, Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $tasks = $this->taskService->paginateForProject($project, $request->validated());

        return $this->success(
            data: TaskResource::collection($tasks)->response()->getData(true),
            message: 'Tasks retrieved successfully.',
        );
    }

    public function store(StoreTaskRequest $request, Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $task = $this->taskService->create($project, $request->validated());

        return $this->success(
            data: new TaskResource($task),
            message: 'Task created successfully.',
            status: Response::HTTP_CREATED,
        );
    }

    public function show(Project $project, Task $task): JsonResponse
    {
        $this->authorize('view', $task);

        return $this->success(
            data: new TaskResource($task),
            message: 'Task retrieved successfully.',
        );
    }

    public function update(UpdateTaskRequest $request, Project $project, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $task = $this->taskService->update($task, $request->validated());

        return $this->success(
            data: new TaskResource($task),
            message: 'Task updated successfully.',
        );
    }

    public function destroy(Project $project, Task $task): JsonResponse
    {
        $this->authorize('delete', $task);

        $this->taskService->delete($task);

        return $this->success(message: 'Task deleted successfully.');
    }
}