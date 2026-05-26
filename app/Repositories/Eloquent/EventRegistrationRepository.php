<?php

namespace App\Repositories\Eloquent;

use App\Enums\EventRegistrationStatusEnum;
use App\Models\EventRegistration;
use App\Repositories\Interfaces\EventRegistrationRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EventRegistrationRepository implements EventRegistrationRepositoryInterface
{
    public function findByEventAndStudent(int $eventId, int $studentId): ?EventRegistration
    {
        return EventRegistration::query()
            ->where('event_id', $eventId)
            ->where('student_id', $studentId)
            ->first();
    }

    public function getByEvent(int $eventId): Collection
    {
        return EventRegistration::query()
            ->with(['student', 'event'])
            ->where('event_id', $eventId)
            ->where('status', EventRegistrationStatusEnum::REGISTERED->value)
            ->latest()
            ->get();
    }

    public function countRegisteredForEvent(int $eventId): int
    {
        return EventRegistration::query()
            ->where('event_id', $eventId)
            ->where('status', EventRegistrationStatusEnum::REGISTERED->value)
            ->count();
    }

    public function create(array $data): EventRegistration
    {
        return EventRegistration::query()->create($data)->load(['student', 'event']);
    }

    public function update(EventRegistration $registration, array $data): EventRegistration
    {
        $registration->update($data);

        return $registration->refresh()->load(['student', 'event']);
    }
}
