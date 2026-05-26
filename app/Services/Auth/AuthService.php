<?php

namespace App\Services\Auth;

use App\Enums\UserStatusEnum;
use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Carbon\Carbon;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AuthService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {
    }

    public function register(array $data): array
    {
        $user = $this->userRepository->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'],
            'status' => UserStatusEnum::ACTIVE->value,
            'email_verified_at' => now(),
        ]);

        return $this->buildAuthResponse($user);
    }

    public function login(array $data): array
    {
        $user = $this->userRepository->findByEmail($data['email']);

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw new AuthenticationException('Invalid credentials.');
        }

        if ($user->status !== UserStatusEnum::ACTIVE->value) {
            throw new HttpException(403, 'Your account is inactive.');
        }

        return $this->buildAuthResponse($user);
    }

    public function logout(string $token): void
    {
        $payload = $this->decodeToken($token);
        $this->blacklistToken($payload);
    }

    public function refresh(string $token): array
    {
        $authenticated = $this->authenticateToken($token);
        $this->blacklistToken($authenticated['payload']);

        return $this->buildAuthResponse($authenticated['user']);
    }

    public function me(User $user): User
    {
        return $user;
    }

    public function authenticateToken(string $token): array
    {
        $payload = $this->decodeToken($token);

        if ($this->isTokenBlacklisted($payload['jti'])) {
            throw new AuthenticationException('Token has been invalidated.');
        }

        $user = $this->userRepository->findById((int) $payload['sub']);

        if (! $user || $user->status !== UserStatusEnum::ACTIVE->value) {
            throw new AuthenticationException('User is not authorized.');
        }

        return [
            'user' => $user,
            'payload' => $payload,
        ];
    }

    public function issueToken(User $user): array
    {
        $now = Carbon::now();
        $expiresAt = $now->copy()->addMinutes((int) config('jwt.ttl'));
        $payload = [
            'iss' => config('app.url'),
            'sub' => $user->id,
            'role' => $user->role,
            'jti' => (string) Str::uuid(),
            'iat' => $now->timestamp,
            'nbf' => $now->timestamp,
            'exp' => $expiresAt->timestamp,
            'type' => 'access',
        ];

        return [
            'access_token' => JWT::encode($payload, $this->getJwtSecret(), 'HS256'),
            'token_type' => 'Bearer',
            'expires_in' => $expiresAt->diffInSeconds($now),
        ];
    }

    private function buildAuthResponse(User $user): array
    {
        return array_merge($this->issueToken($user), [
            'user' => $user,
        ]);
    }

    private function decodeToken(string $token): array
    {
        try {
            $decoded = (array) JWT::decode($token, new Key($this->getJwtSecret(), 'HS256'));
        } catch (ExpiredException) {
            throw new AuthenticationException('Token has expired.');
        } catch (\Throwable) {
            throw new AuthenticationException('Invalid token.');
        }

        if (($decoded['type'] ?? null) !== 'access') {
            throw new AuthenticationException('Invalid token type.');
        }

        return $decoded;
    }

    private function blacklistToken(array $payload): void
    {
        $seconds = max(1, ((int) $payload['exp']) - now()->timestamp);
        Cache::put($this->getBlacklistKey($payload['jti']), true, now()->addSeconds($seconds));
    }

    private function isTokenBlacklisted(string $jti): bool
    {
        return Cache::has($this->getBlacklistKey($jti));
    }

    private function getBlacklistKey(string $jti): string
    {
        return 'jwt_blacklist_'.$jti;
    }

    private function getJwtSecret(): string
    {
        $secret = config('jwt.secret');

        if ($secret) {
            return $secret;
        }

        $appKey = config('app.key');

        if (str_starts_with($appKey, 'base64:')) {
            return base64_decode(substr($appKey, 7), true) ?: $appKey;
        }

        return $appKey;
    }
}
