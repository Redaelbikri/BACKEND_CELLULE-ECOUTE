<?php

namespace App\Repositories\Eloquent;

use App\Enums\AppointmentStatusEnum;
use App\Enums\RoleEnum;
use App\Models\Appointment;
use App\Models\User;
use App\Repositories\Interfaces\AppointmentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class AppointmentRepository implements AppointmentRepositoryInterface
{
    public function getAllForUser(User $user): Collection
    {
        return Appointment::query()
            ->with(['student', 'counselor', 'feedbacks.author', 'feedbacks.target', 'latestEmotionAnalysis'])
            ->when(
                $user->role === RoleEnum::STUDENT->value,
                fn ($query) => $query->where('student_id', $user->id)
            )
            ->when(
                $user->role === RoleEnum::COUNSELOR->value,
                fn ($query) => $query->where('counselor_id', $user->id)
            )
            ->latest('appointment_date')
            ->latest('appointment_time')
            ->get();
    }

    public function findById(int $id): ?Appointment
    {
        return Appointment::query()
            ->with(['student', 'counselor', 'feedbacks.author', 'feedbacks.target', 'latestEmotionAnalysis'])
            ->find($id);
    }

    public function hasStudentCounselorHistory(int $studentId, int $counselorId): bool
    {
        return Appointment::query()
            ->where('student_id', $studentId)
            ->where('counselor_id', $counselorId)
            ->exists();
    }

    public function hasConflict(
        int $counselorId,
        string $appointmentDate,
        string $appointmentTime,
        ?int $excludeAppointmentId = null
    ): bool {
        return Appointment::query()
            ->where('counselor_id', $counselorId)
            ->whereDate('appointment_date', $appointmentDate)
            ->where('appointment_time', $appointmentTime)
            ->whereIn('status', [
                AppointmentStatusEnum::PENDING->value,
                AppointmentStatusEnum::ACCEPTED->value,
            ])
            ->when($excludeAppointmentId, fn ($query) => $query->where('id', '!=', $excludeAppointmentId))
            ->exists();
    }

    public function create(array $data): Appointment
    {
        return Appointment::query()->create($data)->load(['student', 'counselor', 'feedbacks.author', 'feedbacks.target', 'latestEmotionAnalysis']);
    }

    public function update(Appointment $appointment, array $data): Appointment
    {
        $appointment->update($data);

        return $appointment->refresh()->load(['student', 'counselor', 'feedbacks.author', 'feedbacks.target', 'latestEmotionAnalysis']);
    }

    public function delete(Appointment $appointment): void
    {
        $appointment->delete();
    }
}
