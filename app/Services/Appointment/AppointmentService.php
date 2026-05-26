<?php

namespace App\Services\Appointment;

use App\Enums\AppointmentStatusEnum;
use App\Enums\RoleEnum;
use App\Enums\UserStatusEnum;
use App\Models\Appointment;
use App\Models\User;
use App\Repositories\Interfaces\AppointmentRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\Ai\EmotionAnalysisService;
use App\Services\Notification\EmailNotificationService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AppointmentService
{
    public function __construct(
        private readonly AppointmentRepositoryInterface $appointmentRepository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly EmailNotificationService $emailNotificationService,
        private readonly EmotionAnalysisService $emotionAnalysisService
    ) {
    }

    public function index(User $user): Collection
    {
        return $this->appointmentRepository->getAllForUser($user);
    }

    public function show(User $user, int $id): Appointment
    {
        $appointment = $this->findAppointment($id);
        $this->authorizeView($user, $appointment);

        return $appointment;
    }

    public function create(User $user, array $data): Appointment
    {
        if ($user->role !== RoleEnum::STUDENT->value) {
            throw new HttpException(403, 'Only students can create appointments.');
        }

        $counselor = $this->findCounselor((int) $data['counselor_id']);
        $this->ensureFutureDateTime($data['appointment_date'], $data['appointment_time']);

        $this->ensureNoConflict($counselor->id, $data['appointment_date'], $data['appointment_time']);

        $appointment = $this->appointmentRepository->create([
            'student_id' => $user->id,
            'counselor_id' => $counselor->id,
            'appointment_date' => $data['appointment_date'],
            'appointment_time' => $data['appointment_time'],
            'type' => $data['type'],
            'reason' => $data['reason'],
            'status' => AppointmentStatusEnum::PENDING->value,
            'notes' => $data['notes'] ?? null,
        ]);

        $this->emailNotificationService->sendAppointmentCreated($appointment);
        $this->analyzeAppointmentReasonSafely($appointment);

        return $this->findAppointment($appointment->id);
    }

    public function update(User $user, int $id, array $data): Appointment
    {
        if ($user->role !== RoleEnum::ADMIN->value) {
            throw new HttpException(403, 'Only admin can update appointments.');
        }

        $appointment = $this->findAppointment($id);

        if (isset($data['counselor_id'])) {
            $this->findCounselor((int) $data['counselor_id']);
        }

        $date = $data['appointment_date'] ?? $appointment->appointment_date->format('Y-m-d');
        $time = $data['appointment_time'] ?? $appointment->appointment_time;

        $this->ensureFutureDateTime($date, $time, $appointment);

        $nextCounselorId = (int) ($data['counselor_id'] ?? $appointment->counselor_id);
        $nextStatus = $data['status'] ?? $appointment->status;

        if (in_array($nextStatus, [AppointmentStatusEnum::PENDING->value, AppointmentStatusEnum::ACCEPTED->value], true)) {
            $this->ensureNoConflict($nextCounselorId, $date, $time, $appointment->id);
        }

        return $this->appointmentRepository->update($appointment, [
            'counselor_id' => $nextCounselorId,
            'appointment_date' => $date,
            'appointment_time' => $time,
            'type' => $data['type'] ?? $appointment->type,
            'reason' => $data['reason'] ?? $appointment->reason,
            'status' => $nextStatus,
            'notes' => array_key_exists('notes', $data) ? $data['notes'] : $appointment->notes,
        ]);
    }

    public function delete(User $user, int $id): void
    {
        if ($user->role !== RoleEnum::ADMIN->value) {
            throw new HttpException(403, 'Only admin can delete appointments.');
        }

        $appointment = $this->findAppointment($id);
        $this->appointmentRepository->delete($appointment);
    }

    public function accept(User $user, int $id): Appointment
    {
        if ($user->role !== RoleEnum::COUNSELOR->value) {
            throw new HttpException(403, 'Only counselors can accept appointments.');
        }

        $appointment = $this->findAppointment($id);
        $this->ensureCounselorOwnsAppointment($user, $appointment);
        $this->ensureStatusNotClosed($appointment);

        $this->ensureNoConflict(
            $appointment->counselor_id,
            $appointment->appointment_date->format('Y-m-d'),
            $appointment->appointment_time,
            $appointment->id
        );

        $updatedAppointment = $this->appointmentRepository->update($appointment, [
            'status' => AppointmentStatusEnum::ACCEPTED->value,
        ]);

        $this->emailNotificationService->sendAppointmentAccepted($updatedAppointment);

        return $updatedAppointment;
    }

    public function reject(User $user, int $id): Appointment
    {
        if ($user->role !== RoleEnum::COUNSELOR->value) {
            throw new HttpException(403, 'Only counselors can reject appointments.');
        }

        $appointment = $this->findAppointment($id);
        $this->ensureCounselorOwnsAppointment($user, $appointment);
        $this->ensureStatusNotClosed($appointment);

        $updatedAppointment = $this->appointmentRepository->update($appointment, [
            'status' => AppointmentStatusEnum::REJECTED->value,
        ]);

        $this->emailNotificationService->sendAppointmentRejected($updatedAppointment);

        return $updatedAppointment;
    }

    public function cancel(User $user, int $id): Appointment
    {
        $appointment = $this->findAppointment($id);

        if (
            $user->role !== RoleEnum::ADMIN->value &&
            ! ($user->role === RoleEnum::STUDENT->value && $appointment->student_id === $user->id)
        ) {
            throw new HttpException(403, 'You cannot cancel this appointment.');
        }

        if (in_array($appointment->status, [AppointmentStatusEnum::COMPLETED->value, AppointmentStatusEnum::REJECTED->value], true)) {
            throw new HttpException(422, 'This appointment cannot be cancelled.');
        }

        $updatedAppointment = $this->appointmentRepository->update($appointment, [
            'status' => AppointmentStatusEnum::CANCELLED->value,
        ]);

        $this->emailNotificationService->sendAppointmentCancelled($updatedAppointment, $user->role);

        return $updatedAppointment;
    }

    public function complete(User $user, int $id): Appointment
    {
        if ($user->role !== RoleEnum::COUNSELOR->value) {
            throw new HttpException(403, 'Only counselors can complete appointments.');
        }

        $appointment = $this->findAppointment($id);
        $this->ensureCounselorOwnsAppointment($user, $appointment);

        if ($appointment->status !== AppointmentStatusEnum::ACCEPTED->value) {
            throw new HttpException(422, 'Only accepted appointments can be completed.');
        }

        return $this->appointmentRepository->update($appointment, [
            'status' => AppointmentStatusEnum::COMPLETED->value,
        ]);
    }

    private function findAppointment(int $id): Appointment
    {
        $appointment = $this->appointmentRepository->findById($id);

        if (! $appointment) {
            throw new HttpException(404, 'Appointment not found.');
        }

        return $appointment;
    }

    private function authorizeView(User $user, Appointment $appointment): void
    {
        if ($user->role === RoleEnum::ADMIN->value) {
            return;
        }

        if ($user->role === RoleEnum::STUDENT->value && $appointment->student_id === $user->id) {
            return;
        }

        if ($user->role === RoleEnum::COUNSELOR->value && $appointment->counselor_id === $user->id) {
            return;
        }

        throw new HttpException(403, 'You cannot access this appointment.');
    }

    private function ensureCounselorOwnsAppointment(User $user, Appointment $appointment): void
    {
        if ($appointment->counselor_id !== $user->id) {
            throw new HttpException(403, 'You cannot manage this appointment.');
        }
    }

    private function ensureStatusNotClosed(Appointment $appointment): void
    {
        if (in_array($appointment->status, [AppointmentStatusEnum::CANCELLED->value, AppointmentStatusEnum::COMPLETED->value], true)) {
            throw new HttpException(422, 'This appointment status cannot be changed.');
        }
    }

    private function findCounselor(int $id): User
    {
        $counselor = $this->userRepository->findById($id);

        if (
            ! $counselor ||
            $counselor->role !== RoleEnum::COUNSELOR->value ||
            $counselor->status !== UserStatusEnum::ACTIVE->value
        ) {
            throw new HttpException(422, 'Selected counselor is invalid.');
        }

        return $counselor;
    }

    private function ensureFutureDateTime(string $date, string $time, ?Appointment $appointment = null): void
    {
        $appointmentDateTime = Carbon::createFromFormat('Y-m-d H:i', $date.' '.$time);

        if (! $appointmentDateTime) {
            throw new HttpException(422, 'Invalid appointment date or time.');
        }

        if ($appointment && $appointment->status === AppointmentStatusEnum::COMPLETED->value) {
            throw new HttpException(422, 'Completed appointments cannot be rescheduled.');
        }

        if ($appointmentDateTime->isPast()) {
            throw new HttpException(422, 'Appointments cannot be created in the past.');
        }
    }

    private function ensureNoConflict(
        int $counselorId,
        string $appointmentDate,
        string $appointmentTime,
        ?int $excludeAppointmentId = null
    ): void {
        if (
            $this->appointmentRepository->hasConflict(
                $counselorId,
                $appointmentDate,
                $appointmentTime,
                $excludeAppointmentId
            )
        ) {
            throw new HttpException(
                422,
                'Ce conseiller est deja occupe a cette date et a cette heure. Veuillez choisir un autre creneau.'
            );
        }
    }

    private function analyzeAppointmentReasonSafely(Appointment $appointment): void
    {
        try {
            $this->emotionAnalysisService->analyzeAppointmentReason($appointment);
        } catch (\Throwable $exception) {
            Log::warning('Appointment emotion analysis failed.', [
                'appointment_id' => $appointment->id,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
