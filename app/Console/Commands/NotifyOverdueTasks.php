<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Task;
use App\Notifications\TaskOverdueNotification;
use Illuminate\Console\Command;

class NotifyOverdueTasks extends Command
{
    protected $signature = 'tasks:notify-overdue';

    protected $description = 'Send notifications for tasks that have become overdue';

    public function handle(): int
    {
        $count = 0;

        Task::query()
            ->overdue()
            ->with('project.user')
            ->chunkById(100, function ($tasks) use (&$count): void {
                foreach ($tasks as $task) {
                    $task->project->user->notify(new TaskOverdueNotification($task));
                    $count++;
                }
            });

        $this->info("Dispatched overdue notifications for {$count} task(s).");

        return self::SUCCESS;
    }
}
