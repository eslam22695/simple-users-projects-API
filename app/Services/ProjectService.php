<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Project;
use App\Models\User;
use App\Repositories\Contracts\ProjectRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProjectService
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projects,
    ) {}

    /**
     * @return LengthAwarePaginator<int, Project>
     */
    public function paginateForUser(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $this->projects->paginateForUser($user, $perPage);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(User $user, array $data): Project
    {
        return $this->projects->create($user, $data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Project $project, array $data): Project
    {
        return $this->projects->update($project, $data);
    }

    public function delete(Project $project): void
    {
        $this->projects->delete($project);
    }
}
