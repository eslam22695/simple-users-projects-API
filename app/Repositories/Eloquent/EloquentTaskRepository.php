<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
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

    /**
     * @return array{total: int, completed: int, pending: int, overdue: int}
     */
    public function statsForUser(User $user): array
    {
        $counts = Task::query()
            ->whereHas('project', fn (Builder $query) => $query->where('user_id', $user->id))
            ->selectRaw('count(*) as total')
            ->selectRaw('count(case when status = ? then 1 end) as completed', [TaskStatus::Done->value])
            ->selectRaw('count(case when status != ? then 1 end) as pending', [TaskStatus::Done->value])
            ->selectRaw('count(case when status != ? and due_date < ? then 1 end) as overdue', [TaskStatus::Done->value, now()])
            ->first();

        return [
            'total' => (int) $counts->total,
            'completed' => (int) $counts->completed,
            'pending' => (int) $counts->pending,
            'overdue' => (int) $counts->overdue,
        ];
    }
}