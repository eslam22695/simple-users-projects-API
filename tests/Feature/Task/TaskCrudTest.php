<?php

declare(strict_types=1);

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\deleteJson;
use function Pest\Laravel\postJson;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    Sanctum::actingAs($this->user);
    $this->project = Project::factory()->for($this->user)->create();
});

it('creates a task under a project', function (): void {
    postJson("/api/projects/{$this->project->id}/tasks", [
        'title' => 'Fix bug',
        'priority' => 'high',
        'status' => 'todo',
    ])->assertCreated()
        ->assertJsonPath('data.title', 'Fix bug');

    $this->assertDatabaseHas('tasks', [
        'title' => 'Fix bug',
        'project_id' => $this->project->id,
    ]);
});

it('soft deletes a task', function (): void {
    $task = Task::factory()->for($this->project)->create();

    deleteJson("/api/projects/{$this->project->id}/tasks/{$task->id}")->assertOk();

    $this->assertSoftDeleted($task);
});

it('returns 404 for a task that belongs to another project', function (): void {
    $otherProject = Project::factory()->for($this->user)->create();
    $task = Task::factory()->for($otherProject)->create();

    // نحاول نوصلها عن طريق المشروع الغلط
    deleteJson("/api/projects/{$this->project->id}/tasks/{$task->id}")
        ->assertNotFound();
});
