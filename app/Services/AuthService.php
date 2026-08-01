<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    /**
     * @param  array{name: string, email: string, password: string}  $data
     */
    public function register(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'], // يتهَش تلقائياً عبر cast 'hashed'
        ]);
    }

    /**
     * @param  array{email: string, password: string}  $credentials
     *
     * @throws AuthenticationException
     */
    public function authenticate(array $credentials): User
    {
        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw new AuthenticationException('The provided credentials are incorrect.');
        }

        return $user;
    }

    public function createToken(User $user): string
    {
        return $user->createToken('api-token')->plainTextToken;
    }
}
