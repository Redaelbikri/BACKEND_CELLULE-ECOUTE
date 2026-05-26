<?php

namespace App\Services\Feedback;

use App\Enums\AppointmentStatusEnum;
use App\Enums\RoleEnum;
use App\Models\Feedback;
use App\Models\User;
use App\Repositories\Interfaces\AppointmentRepositoryInterface;
use App\Repositories\Interfaces\FeedbackRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpKernel\Exception\HttpException;

class FeedbackService
{
    public function __construct(
        private readonly FeedbackRepositoryInterface $feedbackRepository,
        private readonly AppointmentRepositoryInterface $appointmentRepository
    ) {
    }

    public function index(User $user): Collection
    {
        return $this->feedbackRepository->getAllForUser($user);
    }

    public function show(User $user, int $id): Feedback
    {
        $feedback = $this->feedbackRepository->findById($id);

        if (! $feedback) {
            throw new HttpException(404, 'Feedback not found.');
        }

        $this->authorizeView($user, $feedback);

        return $feedback;
    }

    public function create(User $user, array $data): Feedback
    {
        if (! in_array($user->role, [RoleEnum::STUDENT->value, RoleEnum::COUNSELOR->value], true)) {
            throw new HttpException(403, 'Only students and counselors can create feedback.');
        }

        $appointment = $this->appointmentRepository->findById((int) $data['appointment_id']);

        if (! $appointment) {
            throw new HttpException(404, 'Appointment not found.');
        }

        if ($appointment->status !== AppointmentStatusEnum::COMPLETED->value) {
            throw new HttpException(422, 'Feedback is available only for completed appointments.');
        }

        if ($user->role === RoleEnum::STUDENT->value && $appointment->student_id !== $user->id) {
            throw new HttpException(403, 'You cannot create feedback for this appointment.');
        }

        if ($user->role === RoleEnum::COUNSELOR->value && $appointment->counselor_id !== $user->id) {
            throw new HttpException(403, 'You cannot create feedback for this appointment.');
        }

        if ($this->feedbackRepository->findByAppointmentAndAuthor($appointment->id, $user->id)) {
            throw new HttpException(422, 'You have already submitted feedback for this appointment.');
        }

        $targetId = $user->role === RoleEnum::STUDENT->value
            ? $appointment->counselor_id
            : $appointment->student_id;

        return $this->feedbackRepository->create([
            'appointment_id' => $appointment->id,
            'author_id' => $user->id,
            'target_id' => $targetId,
            'author_role' => $user->role,
            'rating' => $data['rating'],
            'comment' => $data['comment'],
        ]);
    }

    public function delete(User $user, int $id): void
    {
        if ($user->role !== RoleEnum::ADMIN->value) {
            throw new HttpException(403, 'Only admin can delete feedback.');
        }

        $feedback = $this->feedbackRepository->findById($id);

        if (! $feedback) {
            throw new HttpException(404, 'Feedback not found.');
        }

        $this->feedbackRepository->delete($feedback);
    }

    private function authorizeView(User $user, Feedback $feedback): void
    {
        if ($user->role === RoleEnum::ADMIN->value) {
            return;
        }

        if (
            $user->role === RoleEnum::STUDENT->value &&
            $feedback->appointment?->student_id === $user->id
        ) {
            return;
        }

        if (
            $user->role === RoleEnum::COUNSELOR->value &&
            $feedback->appointment?->counselor_id === $user->id
        ) {
            return;
        }

        throw new HttpException(403, 'You cannot access this feedback.');
    }
}
