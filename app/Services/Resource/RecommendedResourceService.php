<?php

namespace App\Services\Resource;

use App\Enums\RoleEnum;
use App\Enums\UserStatusEnum;
use App\Models\RecommendedResource;
use App\Models\User;
use App\Repositories\Interfaces\AppointmentRepositoryInterface;
use App\Repositories\Interfaces\RecommendedResourceRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RecommendedResourceService
{
    public function __construct(
        private readonly RecommendedResourceRepositoryInterface $recommendedResourceRepository,
        private readonly AppointmentRepositoryInterface $appointmentRepository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly EducationalResourceService $educationalResourceService
    ) {
    }

    public function studentIndex(User $user): Collection
    {
        $this->ensureRole($user, RoleEnum::STUDENT);

        return $this->recommendedResourceRepository->getForStudent($user->id);
    }

    public function counselorStudentIndex(User $user, int $studentId): Collection
    {
        $this->ensureFollowedStudent($user, $studentId);

        return $this->recommendedResourceRepository->getForCounselorStudent($user->id, $studentId);
    }

    public function recommend(User $user, int $studentId, array $data): RecommendedResource
    {
        $this->ensureFollowedStudent($user, $studentId);
        $this->educationalResourceService->findPublished((int) $data['resource_id']);

        return $this->recommendedResourceRepository->recommend(
            $studentId,
            $user->id,
            (int) $data['resource_id'],
            $data['note'] ?? null
        );
    }

    private function ensureFollowedStudent(User $user, int $studentId): void
    {
        $this->ensureRole($user, RoleEnum::COUNSELOR);
        $student = $this->userRepository->findById($studentId);

        if (! $student || $student->role !== RoleEnum::STUDENT->value || $student->status !== UserStatusEnum::ACTIVE->value) {
            throw new HttpException(404, 'Student not found.');
        }

        if (! $this->appointmentRepository->hasStudentCounselorHistory($studentId, $user->id)) {
            throw new HttpException(403, 'You can only access followed students.');
        }
    }

    private function ensureRole(User $user, RoleEnum $role): void
    {
        if ($user->role !== $role->value) {
            throw new HttpException(403, 'You are not authorized to access this resource.');
        }
    }
}
