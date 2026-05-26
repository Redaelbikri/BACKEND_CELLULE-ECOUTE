<?php

namespace App\Repositories\Interfaces;

use App\Models\Feedback;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface FeedbackRepositoryInterface
{
    public function getAllForUser(User $user): Collection;

    public function findById(int $id): ?Feedback;

    public function findByAppointmentAndAuthor(int $appointmentId, int $authorId): ?Feedback;

    public function create(array $data): Feedback;

    public function delete(Feedback $feedback): void;
}
