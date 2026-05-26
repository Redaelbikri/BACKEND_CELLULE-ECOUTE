<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\GoogleMobileLoginRequest;
use App\Http\Resources\UserResource;
use App\Services\Auth\GoogleAuthService;

class GoogleAuthController extends Controller
{
    public function __construct(
        private readonly GoogleAuthService $googleAuthService
    ) {
    }

    public function redirect()
    {
        return $this->googleAuthService->redirect();
    }

    public function callback()
    {
        return redirect()->away($this->googleAuthService->callback());
    }

    public function mobileLogin(GoogleMobileLoginRequest $request)
    {
        $result = $this->googleAuthService->mobileLogin($request->validated());

        return response()->json([
            'access_token' => $result['access_token'],
            'token_type' => $result['token_type'],
            'expires_in' => $result['expires_in'],
            'user' => UserResource::make($result['user'])->resolve(),
        ]);
    }
}
