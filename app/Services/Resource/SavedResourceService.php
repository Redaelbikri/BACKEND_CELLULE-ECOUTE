<?php

namespace App\Services\Resource;

use App\Enums\RoleEnum;
use App\Models\SavedResource;
use App\Models\User;
use App\Repositories\Interfaces\SavedResourceRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SavedResourceService
{
    public function __construct(
        private readonly SavedResourceRepositoryInterface $savedResourceRepository,
        private readonly EducationalResourceService $educationalResourceService
    ) {
    }

    public function index(User $user): Collection
    {
        $this->ensureStudent($user);

        return $this->savedResourceRepository->getForStudent($user->id);
    }

    public function save(User $user, int $resourceId): SavedResource
    {
        $this->ensureStudent($user);
        $this->educationalResourceService->findPublished($resourceId);

        return $this->savedResourceRepository->save($user->id, $resourceId);
    }

    public function unsave(User $user, int $resourceId): void
    {
        $this->ensureStudent($user);

        $savedResource = $this->savedResourceRepository->find($user->id, $resourceId);

        if (! $savedResource) {
            throw new HttpException(404, 'Saved resource not found.');
        }

        $this->savedResourceRepository->delete($savedResource);
    }

    private function ensureStudent(User $user): void
    {
        if ($user->role !== RoleEnum::STUDENT->value) {
            throw new HttpException(403, 'You are not authorized to access this resource.');
        }
    }
}
