<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\User;
use App\Repositories\Contracts\ProjectRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentProjectRepository implements ProjectRepositoryInterface
{
    /**
     * @return LengthAwarePaginator<int, Project>
     */
    public function paginateForUser(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $user->projects()
            ->withCount('tasks')
            ->latest()
            ->paginate($perPage);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(User $user, array $data): Project
    {
        /** @var Project $project */
        $project = $user->projects()->create($data);

        return $project;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Project $project, array $data): Project
    {
        $project->update($data);

        return $project->refresh();
    }

    public function delete(Project $project): void
    {
        $project->delete();
    }

    /**
     * @return array{total: int, active: int}
     */
    public function statsForUser(User $user): array
    {
        return [
            'total' => $user->projects()->count(),
            'active' => $user->projects()->where('status', ProjectStatus::Active)->count(),
        ];
    }
}
