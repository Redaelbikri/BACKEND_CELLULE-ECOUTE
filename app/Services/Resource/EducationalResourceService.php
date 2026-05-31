<?php

namespace App\Services\Resource;

use App\Enums\RoleEnum;
use App\Models\EducationalResource;
use App\Models\User;
use App\Repositories\Interfaces\EducationalResourceRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;

class EducationalResourceService
{
    public function __construct(
        private readonly EducationalResourceRepositoryInterface $resourceRepository
    ) {
    }

    public function adminIndex(User $user, ?string $search = null, ?string $category = null): Collection
    {
        $this->ensureRole($user, RoleEnum::ADMIN);

        return $this->resourceRepository->getAll($search, $category);
    }

    public function studentIndex(User $user, ?string $search = null, ?string $category = null): Collection
    {
        $this->ensureRole($user, RoleEnum::STUDENT);

        return $this->resourceRepository->getPublished($search, $category);
    }

    public function counselorIndex(User $user, ?string $search = null, ?string $category = null): Collection
    {
        $this->ensureRole($user, RoleEnum::COUNSELOR);

        return $this->resourceRepository->getPublished($search, $category);
    }

    public function create(User $user, array $data): EducationalResource
    {
        $this->ensureRole($user, RoleEnum::ADMIN);

        return $this->resourceRepository->create([
            ...$data,
            'slug' => $this->uniqueSlug($data['title']),
            'reading_time' => $data['reading_time'] ?? 3,
            'is_published' => $data['is_published'] ?? false,
            'created_by' => $user->id,
        ]);
    }

    public function show(User $user, int $id): EducationalResource
    {
        $resource = $this->findResource($id);

        if ($user->role !== RoleEnum::ADMIN->value && ! $resource->is_published) {
            throw new HttpException(404, 'Resource not found.');
        }

        return $resource;
    }

    public function update(User $user, int $id, array $data): EducationalResource
    {
        $this->ensureRole($user, RoleEnum::ADMIN);

        if (isset($data['title'])) {
            $data['slug'] = $this->uniqueSlug($data['title'], $id);
        }

        return $this->resourceRepository->update($this->findResource($id), $data);
    }

    public function delete(User $user, int $id): void
    {
        $this->ensureRole($user, RoleEnum::ADMIN);
        $this->resourceRepository->delete($this->findResource($id));
    }

    public function publish(User $user, int $id): EducationalResource
    {
        $this->ensureRole($user, RoleEnum::ADMIN);

        return $this->resourceRepository->update($this->findResource($id), ['is_published' => true]);
    }

    public function unpublish(User $user, int $id): EducationalResource
    {
        $this->ensureRole($user, RoleEnum::ADMIN);

        return $this->resourceRepository->update($this->findResource($id), ['is_published' => false]);
    }

    public function findPublished(int $id): EducationalResource
    {
        $resource = $this->findResource($id);

        if (! $resource->is_published) {
            throw new HttpException(404, 'Resource not found.');
        }

        return $resource;
    }

    private function findResource(int $id): EducationalResource
    {
        $resource = $this->resourceRepository->findById($id);

        if (! $resource) {
            throw new HttpException(404, 'Resource not found.');
        }

        return $resource;
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $counter = 2;

        while ($this->slugExists($slug, $ignoreId)) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    private function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        $resource = $this->resourceRepository->getAll()
            ->first(fn (EducationalResource $item) => $item->slug === $slug && $item->id !== $ignoreId);

        return (bool) $resource;
    }

    private function ensureRole(User $user, RoleEnum $role): void
    {
        if ($user->role !== $role->value) {
            throw new HttpException(403, 'You are not authorized to access this resource.');
        }
    }
}
