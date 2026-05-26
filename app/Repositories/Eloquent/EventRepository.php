<?php

namespace App\Repositories\Eloquent;

use App\Enums\EventRegistrationStatusEnum;
use App\Enums\EventStatusEnum;
use App\Models\Event;
use App\Repositories\Interfaces\EventRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EventRepository implements EventRepositoryInterface
{
    public function getAll(): Collection
    {
        return $this->baseQuery()
            ->latest('event_date')
            ->latest('start_time')
            ->get();
    }

    public function getAvailableForStudent(int $studentId): Collection
    {
        return $this->baseQuery($studentId)
            ->where('status', EventStatusEnum::UPCOMING->value)
            ->orderBy('event_date')
            ->orderBy('start_time')
            ->get();
    }

    public function getRegisteredForStudent(int $studentId): Collection
    {
        return $this->baseQuery($studentId)
            ->whereHas('registrations', function ($query) use ($studentId) {
                $query
                    ->where('student_id', $studentId)
                    ->where('status', EventRegistrationStatusEnum::REGISTERED->value);
            })
            ->orderBy('event_date')
            ->orderBy('start_time')
            ->get();
    }

    public function getAssignedToCounselor(int $counselorId): Collection
    {
        return $this->baseQuery()
            ->where('counselor_id', $counselorId)
            ->orderBy('event_date')
            ->orderBy('start_time')
            ->get();
    }

    public function findById(int $id): ?Event
    {
        return $this->baseQuery()->find($id);
    }

    public function findByIdForStudent(int $id, int $studentId): ?Event
    {
        return $this->baseQuery($studentId)->find($id);
    }

    public function findByIdWithRegistrations(int $id): ?Event
    {
        return Event::query()
            ->with([
                'counselor',
                'creator',
                'registrations.student',
            ])
            ->withCount([
                'registrations as registered_participants_count' => fn ($query) => $query
                    ->where('status', EventRegistrationStatusEnum::REGISTERED->value),
            ])
            ->find($id);
    }

    public function findByIdWithRegistrationsForCounselor(int $id, int $counselorId): ?Event
    {
        return Event::query()
            ->with([
                'counselor',
                'creator',
                'registrations.student',
            ])
            ->withCount([
                'registrations as registered_participants_count' => fn ($query) => $query
                    ->where('status', EventRegistrationStatusEnum::REGISTERED->value),
            ])
            ->where('counselor_id', $counselorId)
            ->find($id);
    }

    public function create(array $data): Event
    {
        return Event::query()->create($data)->load(['counselor', 'creator']);
    }

    public function update(Event $event, array $data): Event
    {
        $event->update($data);

        return $event->refresh()->load(['counselor', 'creator']);
    }

    public function delete(Event $event): void
    {
        $event->delete();
    }

    public function counselorHasStudent(int $counselorId, int $studentId): bool
    {
        return Event::query()
            ->where('counselor_id', $counselorId)
            ->whereHas('registrations', function ($query) use ($studentId) {
                $query
                    ->where('student_id', $studentId)
                    ->where('status', EventRegistrationStatusEnum::REGISTERED->value);
            })
            ->exists();
    }

    private function baseQuery(?int $studentId = null)
    {
        return Event::query()
            ->with(['counselor', 'creator'])
            ->withCount([
                'registrations as registered_participants_count' => fn ($query) => $query
                    ->where('status', EventRegistrationStatusEnum::REGISTERED->value),
            ])
            ->when(
                $studentId,
                fn ($query) => $query->with([
                    'registrations' => fn ($registrationQuery) => $registrationQuery
                        ->where('student_id', $studentId)
                        ->with('student'),
                ])
            );
    }
}
