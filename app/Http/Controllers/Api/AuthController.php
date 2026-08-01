<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->authService->register($request->validated());
        $token = $this->authService->createToken($user);

        return $this->success(
            data: [
                'user' => new UserResource($user),
                'token' => $token,
            ],
            message: 'Registered successfully.',
            status: Response::HTTP_CREATED,
        );
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = $this->authService->authenticate($request->validated());
        $token = $this->authService->createToken($user);

        return $this->success(
            data: [
                'user' => new UserResource($user),
                'token' => $token,
            ],
            message: 'Logged in successfully.',
        );
    }

    public function logout(Request $request): JsonResponse
    {
        /** @var \Laravel\Sanctum\PersonalAccessToken $token */
        $token = $request->user()->currentAccessToken();
        $token->delete();

        return $this->success(message: 'Logged out successfully.');
    }
}