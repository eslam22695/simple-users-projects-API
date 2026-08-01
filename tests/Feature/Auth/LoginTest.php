<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\postJson;

it('logs in with correct credentials', function (): void {
    User::factory()->create([
        'email' => 'eslam@example.com',
        'password' => Hash::make('password123'),
    ]);

    postJson('/api/auth/login', [
        'email' => 'eslam@example.com',
        'password' => 'password123',
    ])->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['data' => ['user', 'token']]);
});

it('rejects login with wrong password', function (): void {
    User::factory()->create([
        'email' => 'eslam@example.com',
        'password' => Hash::make('password123'),
    ]);

    postJson('/api/auth/login', [
        'email' => 'eslam@example.com',
        'password' => 'wrong-password',
    ])->assertUnauthorized()
        ->assertJsonPath('success', false);
});