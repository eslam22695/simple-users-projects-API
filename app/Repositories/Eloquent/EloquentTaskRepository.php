<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentTaskRepository implements TaskRepositoryInterface
{
    /**
     * @param  array{status?: string, priority?: string, search?: string, per_page?: int}  $filters
     */
    public function paginateForProject(Project $project, array $filters): LengthAwarePaginator
    {
        return $project->tasks()
            ->when(
                isset($filters['status']),
                fn ($query) => $query->status(TaskStatus::from($filters['status'])),
            )
            ->when(
                isset($filters['priority']),
                fn ($query) => $query->priority(TaskPriority::from($filters['priority'])),
            )
            ->when(
                isset($filters['search']),
                fn ($query) => $query->search($filters['search']),
            )
            ->latest()
            ->paginate($filters['per_page'] ?? 15);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(Project $project, array $data): Task
    {
        return $project->tasks()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Task $task, array $data): Task
    {
        $task->update($data);

        return $task->refresh();
    }

    public function delete(Task $task): void
    {
        $task->delete();
    }
}