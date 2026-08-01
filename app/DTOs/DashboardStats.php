<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class DashboardStats
{
    public function __construct(
        public int $totalProjects,
        public int $activeProjects,
        public int $totalTasks,
        public int $completedTasks,
        public int $pendingTasks,
        public int $overdueTasks,
    ) {}

    /**
     * @return array<string, int>
     */
    public function toArray(): array
    {
        return [
            'total_projects' => $this->totalProjects,
            'active_projects' => $this->activeProjects,
            'total_tasks' => $this->totalTasks,
            'completed_tasks' => $this->completedTasks,
            'pending_tasks' => $this->pendingTasks,
            'overdue_tasks' => $this->overdueTasks,
        ];
    }
}