<?php

namespace App\Http\Middleware;

use App\Services\Auth\AuthService;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JwtMiddleware
{
    public function __construct(
        private readonly AuthService $authService
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json([
                'message' => 'Authentication token is required.',
            ], 401);
        }

        try {
            $authenticated = $this->authService->authenticateToken($token);
        } catch (AuthenticationException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 401);
        }

        $request->attributes->set('auth_user', $authenticated['user']);
        $request->attributes->set('auth_payload', $authenticated['payload']);
        $request->setUserResolver(fn () => $authenticated['user']);

        return $next($request);
    }
}
