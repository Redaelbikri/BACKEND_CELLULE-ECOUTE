<?php

namespace App\Repositories\Interfaces;

use App\Models\EventRegistration;
use Illuminate\Database\Eloquent\Collection;

interface EventRegistrationRepositoryInterface
{
    public function findByEventAndStudent(int $eventId, int $studentId): ?EventRegistration;

    public function getByEvent(int $eventId): Collection;

    public function countRegisteredForEvent(int $eventId): int;

    public function create(array $data): EventRegistration;

    public function update(EventRegistration $registration, array $data): EventRegistration;
}
