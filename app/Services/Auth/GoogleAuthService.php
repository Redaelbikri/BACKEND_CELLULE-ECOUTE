<?php

namespace App\Services\Auth;

use App\Enums\RoleEnum;
use App\Enums\UserStatusEnum;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpKernel\Exception\HttpException;

class GoogleAuthService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly AuthService $authService
    ) {
    }

    public function redirect()
    {
        return Socialite::driver('google')
            ->stateless()
            ->redirect();
    }

    public function callback(): string
    {
        $googleUser = Socialite::driver('google')->stateless()->user();
        $user = $this->resolveOrCreateUser([
            'email' => $googleUser->getEmail(),
            'name' => $googleUser->getName() ?: $googleUser->getNickname() ?: 'Google User',
        ]);

        $token = $this->authService->issueToken($user)['access_token'];

        return rtrim(config('app.frontend_url'), '/').'/auth/google/callback?token='.urlencode($token);
    }

    public function mobileLogin(array $data): array
    {
        $googleUser = $this->resolveMobileGoogleUser($data);
        $user = $this->resolveOrCreateUser($googleUser);

        return array_merge($this->authService->issueToken($user), [
            'user' => $user,
        ]);
    }

    private function resolveOrCreateUser(array $googleUser)
    {
        $email = trim((string) ($googleUser['email'] ?? ''));

        if ($email === '') {
            throw new HttpException(422, 'Google account email is required.');
        }

        $user = $this->userRepository->findByEmail($email);

        if (! $user) {
            $user = $this->userRepository->create([
                'name' => trim((string) ($googleUser['name'] ?? 'Google User')) ?: 'Google User',
                'email' => $email,
                'password' => Hash::make(Str::password(24)),
                'role' => RoleEnum::STUDENT->value,
                'status' => UserStatusEnum::ACTIVE->value,
                'email_verified_at' => now(),
            ]);
        }

        if ($user->status !== UserStatusEnum::ACTIVE->value) {
            throw new HttpException(403, 'Your account is inactive.');
        }

        return $user;
    }

    private function resolveMobileGoogleUser(array $data): array
    {
        $accessToken = trim((string) ($data['access_token'] ?? ''));
        $idToken = trim((string) ($data['id_token'] ?? ''));

        if ($accessToken !== '') {
            try {
                $googleUser = Socialite::driver('google')
                    ->stateless()
                    ->userFromToken($accessToken);

                return [
                    'email' => $googleUser->getEmail(),
                    'name' => $googleUser->getName() ?: $googleUser->getNickname() ?: 'Google User',
                ];
            } catch (\Throwable) {
                // Fall back to id_token validation below when available.
            }
        }

        if ($idToken !== '') {
            $response = Http::timeout(15)->get('https://oauth2.googleapis.com/tokeninfo', [
                'id_token' => $idToken,
            ]);

            if ($response->failed()) {
                throw new HttpException(422, 'Invalid Google ID token.');
            }

            $payload = $response->json();
            $configuredClientId = trim((string) config('services.google.client_id', ''));
            $audience = trim((string) ($payload['aud'] ?? ''));

            if ($configuredClientId !== '' && $audience !== '' && $audience !== $configuredClientId) {
                throw new HttpException(422, 'Google token audience mismatch.');
            }

            return [
                'email' => $payload['email'] ?? null,
                'name' => $payload['name'] ?? $payload['given_name'] ?? 'Google User',
            ];
        }

        throw new HttpException(422, 'Google mobile authentication requires a valid token.');
    }
}
