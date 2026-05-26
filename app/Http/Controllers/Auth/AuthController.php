<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\Auth\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {
    }

    public function register(RegisterRequest $request)
    {
        $result = $this->authService->register($request->validated());

        return response()->json([
            'access_token' => $result['access_token'],
            'token_type' => $result['token_type'],
            'expires_in' => $result['expires_in'],
            'user' => UserResource::make($result['user'])->resolve(),
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $result = $this->authService->login($request->validated());

        return response()->json([
            'access_token' => $result['access_token'],
            'token_type' => $result['token_type'],
            'expires_in' => $result['expires_in'],
            'user' => UserResource::make($result['user'])->resolve(),
        ]);
    }

    public function logout(Request $request)
    {
        $this->authService->logout($request->bearerToken());

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    public function refresh(Request $request)
    {
        $result = $this->authService->refresh($request->bearerToken());

        return response()->json([
            'access_token' => $result['access_token'],
            'token_type' => $result['token_type'],
            'expires_in' => $result['expires_in'],
            'user' => UserResource::make($result['user'])->resolve(),
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => UserResource::make($this->authService->me($request->user()))->resolve(),
        ]);
    }
}
