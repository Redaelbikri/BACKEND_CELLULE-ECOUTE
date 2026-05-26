<?php

namespace App\Services\Event;

use App\Enums\EventRegistrationStatusEnum;
use App\Enums\EventStatusEnum;
use App\Enums\RoleEnum;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use App\Repositories\Interfaces\EventRegistrationRepositoryInterface;
use App\Repositories\Interfaces\EventRepositoryInterface;
use Carbon\Carbon;
use Symfony\Component\HttpKernel\Exception\HttpException;

class EventRegistrationService
{
    public function __construct(
        private readonly EventRepositoryInterface $eventRepository,
        private readonly EventRegistrationRepositoryInterface $eventRegistrationRepository
    ) {
    }

    public function register(User $user, int $eventId): EventRegistration
    {
        $this->ensureStudent($user);

        $event = $this->findStudentEvent($eventId, $user->id);
        $this->ensureEventCanBeRegistered($event);

        $existingRegistration = $this->eventRegistrationRepository->findByEventAndStudent($eventId, $user->id);

        if ($existingRegistration?->status === EventRegistrationStatusEnum::REGISTERED->value) {
            throw new HttpException(422, 'Vous etes deja inscrit a cet evenement.');
        }

        if ($this->eventRegistrationRepository->countRegisteredForEvent($eventId) >= $event->max_participants) {
            throw new HttpException(422, 'Cet evenement a atteint sa capacite maximale.');
        }

        if ($existingRegistration) {
            return $this->eventRegistrationRepository->update($existingRegistration, [
                'status' => EventRegistrationStatusEnum::REGISTERED->value,
            ]);
        }

        return $this->eventRegistrationRepository->create([
            'event_id' => $eventId,
            'student_id' => $user->id,
            'status' => EventRegistrationStatusEnum::REGISTERED->value,
        ]);
    }

    public function cancel(User $user, int $eventId): EventRegistration
    {
        $this->ensureStudent($user);

        $registration = $this->eventRegistrationRepository->findByEventAndStudent($eventId, $user->id);

        if (! $registration || $registration->status !== EventRegistrationStatusEnum::REGISTERED->value) {
            throw new HttpException(404, 'Aucune inscription active trouvee pour cet evenement.');
        }

        return $this->eventRegistrationRepository->update($registration, [
            'status' => EventRegistrationStatusEnum::CANCELLED->value,
        ]);
    }

    private function findStudentEvent(int $eventId, int $studentId): Event
    {
        $event = $this->eventRepository->findByIdForStudent($eventId, $studentId);

        if (! $event) {
            throw new HttpException(404, 'Event not found.');
        }

        return $event;
    }

    private function ensureEventCanBeRegistered(Event $event): void
    {
        if ($event->status !== EventStatusEnum::UPCOMING->value) {
            throw new HttpException(422, 'Les inscriptions sont fermees pour cet evenement.');
        }

        $eventEndDateTime = Carbon::createFromFormat(
            'Y-m-d H:i',
            $event->event_date?->format('Y-m-d').' '.$event->end_time
        );

        if (! $eventEndDateTime || $eventEndDateTime->isPast()) {
            throw new HttpException(422, 'Cet evenement est deja termine.');
        }
    }

    private function ensureStudent(User $user): void
    {
        if ($user->role !== RoleEnum::STUDENT->value) {
            throw new HttpException(403, 'Only students can manage event registrations.');
        }
    }
}
