<?php

declare(strict_types=1);

use App\Models\Project;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\putJson;

beforeEach(function (): void {
    Sanctum::actingAs(User::factory()->create());
    $this->otherUsersProject = Project::factory()->create(); // بتاع يوزر تاني
});

it('forbids viewing another user project', function (): void {
    getJson("/api/projects/{$this->otherUsersProject->id}")
        ->assertForbidden()
        ->assertJsonPath('success', false);
});

it('forbids updating another user project', function (): void {
    putJson("/api/projects/{$this->otherUsersProject->id}", ['name' => 'Hack'])
        ->assertForbidden();
});

it('forbids deleting another user project', function (): void {
    deleteJson("/api/projects/{$this->otherUsersProject->id}")
        ->assertForbidden();
});
