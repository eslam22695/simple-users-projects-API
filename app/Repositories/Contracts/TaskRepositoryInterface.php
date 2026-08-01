<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TaskRepositoryInterface
{
    /**
     * @param  array{status?: string, priority?: string, search?: string, per_page?: int}  $filters
     */
    public function paginateForProject(Project $project, array $filters): LengthAwarePaginator;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(Project $project, array $data): Task;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Task $task, array $data): Task;

    public function delete(Task $task): void;

    /**
     * @return array{total: int, completed: int, pending: int, overdue: int}
     */
    public function statsForUser(User $user): array;
}