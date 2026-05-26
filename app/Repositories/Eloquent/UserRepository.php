<?php

namespace App\Repositories\Eloquent;

use App\Enums\RoleEnum;
use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class UserRepository implements UserRepositoryInterface
{
    public function getAll(?string $role = null): Collection
    {
        return User::query()
            ->when($role, fn ($query) => $query->where('role', $role))
            ->latest()
            ->get();
    }

    public function findById(int $id): ?User
    {
        return User::query()->find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return User::query()->where('email', $email)->first();
    }

    public function create(array $data): User
    {
        return User::query()->create($data);
    }

    public function update(User $user, array $data): User
    {
        $user->update($data);

        return $user->refresh();
    }

    public function delete(User $user): void
    {
        $user->delete();
    }

    public function countByRole(string $role, ?string $status = null): int
    {
        return User::query()
            ->where('role', $role)
            ->when($status, fn ($query) => $query->where('status', $status))
            ->count();
    }

    public function getCounselors(): Collection
    {
        return User::query()
            ->where('role', RoleEnum::COUNSELOR->value)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }
}
