<?php

declare(strict_types=1);

use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskOverdueNotification;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\artisan;

it('notifies the project owner about overdue tasks', function (): void {
    Notification::fake();

    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();

    // مهمة متأخرة
    Task::factory()->for($project)->create([
        'status' => TaskStatus::Todo,
        'due_date' => now()->subDays(2),
    ]);

    // مهمة مش متأخرة — مفروض تتجاهل
    Task::factory()->for($project)->create([
        'status' => TaskStatus::Todo,
        'due_date' => now()->addWeek(),
    ]);

    artisan('tasks:notify-overdue')->assertSuccessful();

    Notification::assertSentTo($user, TaskOverdueNotification::class);
    Notification::assertCount(1);
});
