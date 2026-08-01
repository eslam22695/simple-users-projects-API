<?php

declare(strict_types=1);

use App\Models\User;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\postJson;

it('logs out an authenticated user', function (): void {
    Sanctum::actingAs(User::factory()->create());

    postJson('/api/auth/logout')
        ->assertOk()
        ->assertJsonPath('success', true);
});

it('blocks logout for guests', function (): void {
    postJson('/api/auth/logout')->assertUnauthorized();
});
