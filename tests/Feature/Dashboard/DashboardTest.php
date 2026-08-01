<?php

declare(strict_types=1);

use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\getJson;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    Sanctum::actingAs($this->user);
});

it('returns correct dashboard statistics', function (): void {
    $active = Project::factory()->for($this->user)->create(['status' => ProjectStatus::Active]);
    Project::factory()->for($this->user)->create(['status' => ProjectStatus::Archived]);

    // مكتملة
    Task::factory()->for($active)->create([
        'status' => TaskStatus::Done,
        'due_date' => now()->addDays(5),
    ]);

    // مش مكتملة، لسه مش متأخرة
    Task::factory()->for($active)->create([
        'status' => TaskStatus::Todo,
        'due_date' => now()->addWeek(),
    ]);

    // متأخرة (مش Done + تاريخ ماضي)
    Task::factory()->for($active)->create([
        'status' => TaskStatus::Todo,
        'due_date' => now()->subDays(3),
    ]);

    getJson('/api/dashboard')
        ->assertOk()
        ->assertJsonPath('data.total_projects', 2)
        ->assertJsonPath('data.active_projects', 1)
        ->assertJsonPath('data.total_tasks', 3)
        ->assertJsonPath('data.completed_tasks', 1)
        ->assertJsonPath('data.pending_tasks', 2)
        ->assertJsonPath('data.overdue_tasks', 1);
});

it('excludes other users data from the dashboard', function (): void {
    Project::factory()->count(3)->create(); // ليوزرز تانيين

    getJson('/api/dashboard')
        ->assertOk()
        ->assertJsonPath('data.total_projects', 0);
});
