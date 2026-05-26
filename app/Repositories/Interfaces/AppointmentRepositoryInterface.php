<?php

namespace App\Repositories\Interfaces;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface AppointmentRepositoryInterface
{
    public function getAllForUser(User $user): Collection;

    public function findById(int $id): ?Appointment;

    public function hasStudentCounselorHistory(int $studentId, int $counselorId): bool;

    public function hasConflict(
        int $counselorId,
        string $appointmentDate,
        string $appointmentTime,
        ?int $excludeAppointmentId = null
    ): bool;

    public function create(array $data): Appointment;

    public function update(Appointment $appointment, array $data): Appointment;

    public function delete(Appointment $appointment): void;
}
