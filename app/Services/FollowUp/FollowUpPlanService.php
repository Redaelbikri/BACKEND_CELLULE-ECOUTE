<?php

namespace App\Services\FollowUp;

use App\Enums\AppointmentStatusEnum;
use App\Enums\RoleEnum;
use App\Enums\UserStatusEnum;
use App\Models\FollowUpPlan;
use App\Models\User;
use App\Repositories\Interfaces\AppointmentRepositoryInterface;
use App\Repositories\Interfaces\FollowUpPlanRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpKernel\Exception\HttpException;

class FollowUpPlanService
{
    public function __construct(
        private readonly FollowUpPlanRepositoryInterface $planRepository,
        private readonly AppointmentRepositoryInterface $appointmentRepository,
        private readonly UserRepositoryInterface $userRepository
    ) {
    }

    public function studentIndex(User $user): Collection
    {
        $this->ensureRole($user, RoleEnum::STUDENT);

        return $this->planRepository->getForStudent($user->id);
    }

    public function counselorStudentIndex(User $user, int $studentId): Collection
    {
        $this->ensureFollowedStudent($user, $studentId);

        return $this->planRepository->getForCounselorStudent($user->id, $studentId);
    }

    public function store(User $user, int $studentId, array $data): FollowUpPlan
    {
        $this->ensureFollowedStudent($user, $studentId);
        $this->validateAppointment($user->id, $studentId, $data['appointment_id'] ?? null);

        return $this->planRepository->create([
            ...$data,
            'student_id' => $studentId,
            'counselor_id' => $user->id,
            'status' => $data['status'] ?? 'active',
        ]);
    }

    public function update(User $user, int $studentId, int $id, array $data): FollowUpPlan
    {
        $this->ensureFollowedStudent($user, $studentId);
        $plan = $this->findOwnedPlan($user, $studentId, $id);
        $this->validateAppointment($user->id, $studentId, $data['appointment_id'] ?? $plan->appointment_id);

        return $this->planRepository->update($plan, $data);
    }

    public function complete(User $user, int $studentId, int $id): FollowUpPlan
    {
        $this->ensureFollowedStudent($user, $studentId);

        return $this->planRepository->update($this->findOwnedPlan($user, $studentId, $id), [
            'status' => 'completed',
        ]);
    }

    private function findOwnedPlan(User $user, int $studentId, int $id): FollowUpPlan
    {
        $plan = $this->planRepository->findById($id);

        if (! $plan || $plan->student_id !== $studentId || $plan->counselor_id !== $user->id) {
            throw new HttpException(404, 'Follow-up plan not found.');
        }

        return $plan;
    }

    private function validateAppointment(int $counselorId, int $studentId, ?int $appointmentId): void
    {
        if (! $appointmentId) {
            return;
        }

        $appointment = $this->appointmentRepository->findById($appointmentId);

        if (
            ! $appointment ||
            $appointment->student_id !== $studentId ||
            $appointment->counselor_id !== $counselorId ||
            $appointment->status !== AppointmentStatusEnum::COMPLETED->value
        ) {
            throw new HttpException(422, 'A follow-up plan must be linked to a completed appointment for this student.');
        }
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
