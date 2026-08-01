<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Repositories\Contracts\TaskRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class EloquentTaskRepository implements TaskRepositoryInterface
{
    /**
     * @param  array{status?: string, priority?: string, search?: string, per_page?: int}  $filters
     * @return LengthAwarePaginator<int, Task>
     */
    public function paginateForProject(Project $project, array $filters): LengthAwarePaginator
    {
        return $project->tasks()
            ->when(
                isset($filters['status']),
                fn (Builder $query): Builder => $query->status(TaskStatus::from($filters['status'])),
            )
            ->when(
                isset($filters['priority']),
                fn (Builder $query): Builder => $query->priority(TaskPriority::from($filters['priority'])),
            )
            ->when(
                isset($filters['search']),
                fn (Builder $query): Builder => $query->search($filters['search']),
            )
            ->latest()
            ->paginate($filters['per_page'] ?? 15);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(Project $project, array $data): Task
    {
        /** @var Task $task */
        $task = $project->tasks()->create($data);

        return $task;
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

    /**
     * @return array{total: int, completed: int, pending: int, overdue: int}
     */
    public function statsForUser(User $user): array
    {
        $base = Task::query()
            ->whereHas('project', fn (Builder $query) => $query->where('user_id', $user->id));

        return [
            'total' => (clone $base)->count(),
            'completed' => (clone $base)->where('status', TaskStatus::Done)->count(),
            'pending' => (clone $base)->where('status', '!=', TaskStatus::Done)->count(),
            'overdue' => (clone $base)->overdue()->count(),
        ];
    }
}
