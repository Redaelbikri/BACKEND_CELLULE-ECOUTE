<?php

namespace App\Services\Event;

use App\Enums\EventStatusEnum;
use App\Enums\RoleEnum;
use App\Enums\UserStatusEnum;
use App\Models\Event;
use App\Models\User;
use App\Repositories\Interfaces\EventRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\HttpException;

class EventService
{
    public function __construct(
        private readonly EventRepositoryInterface $eventRepository,
        private readonly UserRepositoryInterface $userRepository
    ) {
    }

    public function adminIndex(User $user): Collection
    {
        $this->ensureRole($user, RoleEnum::ADMIN);

        return $this->eventRepository->getAll();
    }

    public function studentIndex(User $user): Collection
    {
        $this->ensureRole($user, RoleEnum::STUDENT);

        return $this->eventRepository->getAvailableForStudent($user->id);
    }

    public function studentMyEvents(User $user): Collection
    {
        $this->ensureRole($user, RoleEnum::STUDENT);

        return $this->eventRepository->getRegisteredForStudent($user->id);
    }

    public function counselorIndex(User $user): Collection
    {
        $this->ensureRole($user, RoleEnum::COUNSELOR);

        return $this->eventRepository->getAssignedToCounselor($user->id);
    }

    public function show(User $user, int $id): Event
    {
        $this->ensureRole($user, RoleEnum::ADMIN);

        return $this->findEventWithRegistrations($id);
    }

    public function create(User $user, array $data): Event
    {
        $this->ensureRole($user, RoleEnum::ADMIN);
        $this->validateEventData($data['event_date'], $data['start_time'], $data['end_time']);

        if (! empty($data['counselor_id'])) {
            $this->findActiveCounselor((int) $data['counselor_id']);
        }

        $imagePath = $this->storeEventImage($data['image'] ?? null);

        return $this->eventRepository->create([
            ...$this->withoutFilePayload($data),
            'image_path' => $imagePath,
            'status' => $data['status'] ?? EventStatusEnum::UPCOMING->value,
            'created_by' => $user->id,
        ]);
    }

    public function update(User $user, int $id, array $data): Event
    {
        $this->ensureRole($user, RoleEnum::ADMIN);

        $event = $this->findEvent($id);
        $eventDate = $data['event_date'] ?? $event->event_date?->format('Y-m-d');
        $startTime = $data['start_time'] ?? $event->start_time;
        $endTime = $data['end_time'] ?? $event->end_time;
        $maxParticipants = (int) ($data['max_participants'] ?? $event->max_participants);

        $this->validateEventData($eventDate, $startTime, $endTime);

        if (array_key_exists('counselor_id', $data) && ! empty($data['counselor_id'])) {
            $this->findActiveCounselor((int) $data['counselor_id']);
        }

        if (($event->registered_participants_count ?? 0) > $maxParticipants) {
            throw new HttpException(422, 'Le nombre maximal de participants est inferieur aux inscriptions deja actives.');
        }

        $payload = $this->withoutFilePayload($data);

        if (($data['remove_image'] ?? false) && $event->image_path) {
            Storage::disk('public')->delete($event->image_path);
            $payload['image_path'] = null;
        }

        if (! empty($data['image']) && $data['image'] instanceof UploadedFile) {
            $newImagePath = $this->storeEventImage($data['image']);

            if ($event->image_path) {
                Storage::disk('public')->delete($event->image_path);
            }

            $payload['image_path'] = $newImagePath;
        }

        return $this->eventRepository->update($event, $payload);
    }

    public function delete(User $user, int $id): void
    {
        $this->ensureRole($user, RoleEnum::ADMIN);

        $event = $this->findEvent($id);

        if ($event->image_path) {
            Storage::disk('public')->delete($event->image_path);
        }

        $this->eventRepository->delete($event);
    }

    public function cancel(User $user, int $id): Event
    {
        $this->ensureRole($user, RoleEnum::ADMIN);

        return $this->eventRepository->update($this->findEvent($id), [
            'status' => EventStatusEnum::CANCELLED->value,
        ]);
    }

    public function complete(User $user, int $id): Event
    {
        $this->ensureRole($user, RoleEnum::ADMIN);

        return $this->eventRepository->update($this->findEvent($id), [
            'status' => EventStatusEnum::COMPLETED->value,
        ]);
    }

    public function adminRegistrations(User $user, int $id): Event
    {
        $this->ensureRole($user, RoleEnum::ADMIN);

        return $this->findEventWithRegistrations($id);
    }

    public function counselorRegistrations(User $user, int $id): Event
    {
        $this->ensureRole($user, RoleEnum::COUNSELOR);

        $event = $this->eventRepository->findByIdWithRegistrationsForCounselor($id, $user->id);

        if (! $event) {
            throw new HttpException(404, 'Event not found.');
        }

        return $event;
    }

    private function findEvent(int $id): Event
    {
        $event = $this->eventRepository->findById($id);

        if (! $event) {
            throw new HttpException(404, 'Event not found.');
        }

        return $event;
    }

    private function findEventWithRegistrations(int $id): Event
    {
        $event = $this->eventRepository->findByIdWithRegistrations($id);

        if (! $event) {
            throw new HttpException(404, 'Event not found.');
        }

        return $event;
    }

    private function findActiveCounselor(int $id): User
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

    private function validateEventData(string $eventDate, string $startTime, string $endTime): void
    {
        $startDateTime = Carbon::createFromFormat('Y-m-d H:i', $eventDate.' '.$startTime);
        $endDateTime = Carbon::createFromFormat('Y-m-d H:i', $eventDate.' '.$endTime);

        if (! $startDateTime || ! $endDateTime) {
            throw new HttpException(422, 'Invalid event date or time.');
        }

        if ($startDateTime->isPast() && ! $startDateTime->isToday()) {
            throw new HttpException(422, 'La date de l evenement ne peut pas etre dans le passe.');
        }

        if ($startDateTime->isToday() && $endDateTime->isPast()) {
            throw new HttpException(422, 'La fin de l evenement doit etre dans le futur.');
        }

        if ($endDateTime->lessThanOrEqualTo($startDateTime)) {
            throw new HttpException(422, 'L heure de fin doit etre apres l heure de debut.');
        }
    }

    private function ensureRole(User $user, RoleEnum $role): void
    {
        if ($user->role !== $role->value) {
            throw new HttpException(403, 'You are not authorized to access this resource.');
        }
    }

    private function storeEventImage(?UploadedFile $image): ?string
    {
        if (! $image) {
            return null;
        }

        return $image->store('events', 'public');
    }

    private function withoutFilePayload(array $data): array
    {
        unset($data['image'], $data['remove_image']);

        return $data;
    }
}
