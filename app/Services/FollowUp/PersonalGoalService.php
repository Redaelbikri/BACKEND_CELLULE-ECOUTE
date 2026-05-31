<?php

namespace App\Services\FollowUp;

use App\Enums\RoleEnum;
use App\Enums\UserStatusEnum;
use App\Models\PersonalGoal;
use App\Models\User;
use App\Repositories\Interfaces\AppointmentRepositoryInterface;
use App\Repositories\Interfaces\PersonalGoalRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PersonalGoalService
{
    public function __construct(
        private readonly PersonalGoalRepositoryInterface $goalRepository,
        private readonly AppointmentRepositoryInterface $appointmentRepository,
        private readonly UserRepositoryInterface $userRepository
    ) {
    }

    public function studentIndex(User $user): Collection
    {
        $this->ensureRole($user, RoleEnum::STUDENT);

        return $this->goalRepository->getForStudent($user->id);
    }

    public function store(User $user, array $data): PersonalGoal
    {
        $this->ensureRole($user, RoleEnum::STUDENT);

        return $this->goalRepository->create([
            ...$data,
            'student_id' => $user->id,
            'created_by' => $user->id,
            'status' => $data['status'] ?? 'todo',
            'priority' => $data['priority'] ?? 'medium',
        ]);
    }

    public function show(User $user, int $id): PersonalGoal
    {
        $this->ensureRole($user, RoleEnum::STUDENT);

        return $this->findOwnedGoal($user, $id);
    }

    public function update(User $user, int $id, array $data): PersonalGoal
    {
        $this->ensureRole($user, RoleEnum::STUDENT);

        return $this->goalRepository->update($this->findOwnedGoal($user, $id), $data);
    }

    public function updateStatus(User $user, int $id, string $status): PersonalGoal
    {
        $this->ensureRole($user, RoleEnum::STUDENT);

        return $this->goalRepository->update($this->findOwnedGoal($user, $id), ['status' => $status]);
    }

    public function delete(User $user, int $id): void
    {
        $this->ensureRole($user, RoleEnum::STUDENT);
        $this->goalRepository->delete($this->findOwnedGoal($user, $id));
    }

    public function counselorStudentIndex(User $user, int $studentId): Collection
    {
        $this->ensureFollowedStudent($user, $studentId);

        return $this->goalRepository->getForStudent($studentId);
    }

    public function suggest(User $user, int $studentId, array $data): PersonalGoal
    {
        $this->ensureFollowedStudent($user, $studentId);

        return $this->goalRepository->create([
            ...$data,
            'student_id' => $studentId,
            'suggested_by' => $user->id,
            'created_by' => $user->id,
            'status' => $data['status'] ?? 'todo',
            'priority' => $data['priority'] ?? 'medium',
        ]);
    }

    private function findOwnedGoal(User $user, int $id): PersonalGoal
    {
        $goal = $this->goalRepository->findById($id);

        if (! $goal || $goal->student_id !== $user->id) {
            throw new HttpException(404, 'Goal not found.');
        }

        return $goal;
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
