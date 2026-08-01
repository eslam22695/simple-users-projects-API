<?php

declare(strict_types=1);

use App\Models\Project;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    Sanctum::actingAs($this->user);
});

it('lists only the authenticated user projects', function (): void {
    Project::factory()->count(3)->for($this->user)->create();
    Project::factory()->count(2)->create(); // ليوزر تاني

    getJson('/api/projects')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonCount(3, 'data.data');
});

it('creates a project', function (): void {
    postJson('/api/projects', [
        'name' => 'New Project',
        'description' => 'Description here',
        'status' => 'active',
    ])->assertCreated()
        ->assertJsonPath('data.name', 'New Project');

    $this->assertDatabaseHas('projects', [
        'name' => 'New Project',
        'user_id' => $this->user->id,
    ]);
});

it('rejects a project with an invalid status', function (): void {
    postJson('/api/projects', [
        'name' => 'New Project',
        'status' => 'invalid-status',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors('status');
});

it('updates an owned project', function (): void {
    $project = Project::factory()->for($this->user)->create();

    putJson("/api/projects/{$project->id}", ['name' => 'Updated'])
        ->assertOk()
        ->assertJsonPath('data.name', 'Updated');
});

it('soft deletes an owned project', function (): void {
    $project = Project::factory()->for($this->user)->create();

    deleteJson("/api/projects/{$project->id}")->assertOk();

    $this->assertSoftDeleted($project);
});
