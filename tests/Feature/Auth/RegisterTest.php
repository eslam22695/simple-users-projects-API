<?php

declare(strict_types=1);

use App\Models\User;

use function Pest\Laravel\postJson;

it('registers a new user with valid data', function (): void {
    $response = postJson('/api/auth/register', [
        'name' => 'Eslam',
        'email' => 'eslam@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonStructure([
            'data' => ['user' => ['id', 'name', 'email'], 'token'],
        ]);

    expect(User::where('email', 'eslam@example.com')->exists())->toBeTrue();
});

it('rejects registration with an existing email', function (): void {
    User::factory()->create(['email' => 'taken@example.com']);

    postJson('/api/auth/register', [
        'name' => 'Eslam',
        'email' => 'taken@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonValidationErrors('email');
});

it('rejects registration when passwords do not match', function (): void {
    postJson('/api/auth/register', [
        'name' => 'Eslam',
        'email' => 'eslam@example.com',
        'password' => 'password123',
        'password_confirmation' => 'different',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors('password');
});
