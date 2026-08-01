<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Project;
use App\Models\User;
use App\Repositories\Contracts\ProjectRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Enums\ProjectStatus;

class EloquentProjectRepository implements ProjectRepositoryInterface
{
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
        return $user->projects()->create($data);
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
        $counts = $user->projects()
            ->selectRaw('count(*) as total')
            ->selectRaw('count(case when status = ? then 1 end) as active', [ProjectStatus::Active->value])
            ->first();

        return [
            'total' => (int) $counts->total,
            'active' => (int) $counts->active,
        ];
    }
}