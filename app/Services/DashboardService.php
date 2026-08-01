<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\DashboardStats;
use App\Models\User;
use App\Repositories\Contracts\ProjectRepositoryInterface;
use App\Repositories\Contracts\TaskRepositoryInterface;

class DashboardService
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projects,
        private readonly TaskRepositoryInterface $tasks,
    ) {}

    public function statsForUser(User $user): DashboardStats
    {
        $projectStats = $this->projects->statsForUser($user);
        $taskStats = $this->tasks->statsForUser($user);

        return new DashboardStats(
            totalProjects: $projectStats['total'],
            activeProjects: $projectStats['active'],
            totalTasks: $taskStats['total'],
            completedTasks: $taskStats['completed'],
            pendingTasks: $taskStats['pending'],
            overdueTasks: $taskStats['overdue'],
        );
    }
}
