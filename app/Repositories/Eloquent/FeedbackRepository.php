<?php

namespace App\Repositories\Eloquent;

use App\Enums\RoleEnum;
use App\Models\Feedback;
use App\Models\User;
use App\Repositories\Interfaces\FeedbackRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class FeedbackRepository implements FeedbackRepositoryInterface
{
    public function getAllForUser(User $user): Collection
    {
        return Feedback::query()
            ->with(['appointment.student', 'appointment.counselor', 'author', 'target'])
            ->when(
                $user->role === RoleEnum::STUDENT->value,
                fn ($query) => $query->whereHas('appointment', fn ($appointmentQuery) => $appointmentQuery->where('student_id', $user->id))
            )
            ->when(
                $user->role === RoleEnum::COUNSELOR->value,
                fn ($query) => $query->whereHas('appointment', fn ($appointmentQuery) => $appointmentQuery->where('counselor_id', $user->id))
            )
            ->latest()
            ->get();
    }

    public function findById(int $id): ?Feedback
    {
        return Feedback::query()
            ->with(['appointment.student', 'appointment.counselor', 'author', 'target'])
            ->find($id);
    }

    public function findByAppointmentAndAuthor(int $appointmentId, int $authorId): ?Feedback
    {
        return Feedback::query()
            ->where('appointment_id', $appointmentId)
            ->where('author_id', $authorId)
            ->first();
    }

    public function create(array $data): Feedback
    {
        return Feedback::query()
            ->create($data)
            ->load(['appointment.student', 'appointment.counselor', 'author', 'target']);
    }

    public function delete(Feedback $feedback): void
    {
        $feedback->delete();
    }
}
