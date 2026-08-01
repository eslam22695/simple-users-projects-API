<?php

declare(strict_types=1);

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\getJson;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    Sanctum::actingAs($this->user);
    $this->project = Project::factory()->for($this->user)->create();
});

it('filters tasks by status', function (): void {
    Task::factory()->for($this->project)->create(['status' => TaskStatus::Done]);
    Task::factory()->for($this->project)->create(['status' => TaskStatus::Todo]);

    getJson("/api/projects/{$this->project->id}/tasks?status=done")
        ->assertOk()
        ->assertJsonCount(1, 'data.data');
});

it('filters tasks by priority', function (): void {
    Task::factory()->for($this->project)->create(['priority' => TaskPriority::High]);
    Task::factory()->for($this->project)->count(2)->create(['priority' => TaskPriority::Low]);

    getJson("/api/projects/{$this->project->id}/tasks?priority=high")
        ->assertOk()
        ->assertJsonCount(1, 'data.data');
});

it('searches tasks by title', function (): void {
    Task::factory()->for($this->project)->create(['title' => 'Fix login bug']);
    Task::factory()->for($this->project)->create(['title' => 'Update docs']);

    getJson("/api/projects/{$this->project->id}/tasks?search=login")
        ->assertOk()
        ->assertJsonCount(1, 'data.data');
});

it('rejects an invalid status filter', function (): void {
    getJson("/api/projects/{$this->project->id}/tasks?status=banana")
        ->assertUnprocessable()
        ->assertJsonValidationErrors('status');
});
