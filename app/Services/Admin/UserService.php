<?php

namespace App\Services\Admin;

use App\Enums\RoleEnum;
use App\Enums\UserStatusEnum;
use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UserService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {
    }

    public function list(?string $role = null): Collection
    {
        return $this->userRepository->getAll($role);
    }

    public function show(int $id): User
    {
        return $this->findUser($id);
    }

    public function create(array $data): User
    {
        return $this->userRepository->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'],
            'status' => $data['status'] ?? UserStatusEnum::ACTIVE->value,
            'email_verified_at' => now(),
        ]);
    }

    public function update(int $id, array $data): User
    {
        $user = $this->findUser($id);

        if (
            $user->role === RoleEnum::ADMIN->value &&
            ($data['role'] ?? $user->role) !== RoleEnum::ADMIN->value &&
            $this->userRepository->countByRole(RoleEnum::ADMIN->value) <= 1
        ) {
            throw new HttpException(422, 'You cannot remove the last admin role.');
        }

        if (
            $user->role === RoleEnum::ADMIN->value &&
            ($data['status'] ?? $user->status) !== UserStatusEnum::ACTIVE->value &&
            $this->userRepository->countByRole(RoleEnum::ADMIN->value, UserStatusEnum::ACTIVE->value) <= 1
        ) {
            throw new HttpException(422, 'You cannot deactivate the last active admin.');
        }

        $payload = collect($data)
            ->only(['name', 'email', 'password', 'role', 'status'])
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->all();

        return $this->userRepository->update($user, $payload);
    }

    public function delete(int $id): void
    {
        $user = $this->findUser($id);

        if (
            $user->role === RoleEnum::ADMIN->value &&
            $this->userRepository->countByRole(RoleEnum::ADMIN->value) <= 1
        ) {
            throw new HttpException(422, 'You cannot delete the last admin.');
        }

        $this->userRepository->delete($user);
    }

    public function activate(int $id): User
    {
        $user = $this->findUser($id);

        return $this->userRepository->update($user, [
            'status' => UserStatusEnum::ACTIVE->value,
        ]);
    }

    public function deactivate(int $id): User
    {
        $user = $this->findUser($id);

        if (
            $user->role === RoleEnum::ADMIN->value &&
            $this->userRepository->countByRole(RoleEnum::ADMIN->value, UserStatusEnum::ACTIVE->value) <= 1
        ) {
            throw new HttpException(422, 'You cannot deactivate the last active admin.');
        }

        return $this->userRepository->update($user, [
            'status' => UserStatusEnum::INACTIVE->value,
        ]);
    }

    public function counselors(): Collection
    {
        return $this->userRepository->getCounselors();
    }

    private function findUser(int $id): User
    {
        $user = $this->userRepository->findById($id);

        if (! $user) {
            throw new HttpException(404, 'User not found.');
        }

        return $user;
    }
}
