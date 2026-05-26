<?php

namespace App\Repositories\Interfaces;

use App\Models\Event;
use Illuminate\Database\Eloquent\Collection;

interface EventRepositoryInterface
{
    public function getAll(): Collection;

    public function getAvailableForStudent(int $studentId): Collection;

    public function getRegisteredForStudent(int $studentId): Collection;

    public function getAssignedToCounselor(int $counselorId): Collection;

    public function findById(int $id): ?Event;

    public function findByIdForStudent(int $id, int $studentId): ?Event;

    public function findByIdWithRegistrations(int $id): ?Event;

    public function findByIdWithRegistrationsForCounselor(int $id, int $counselorId): ?Event;

    public function create(array $data): Event;

    public function update(Event $event, array $data): Event;

    public function delete(Event $event): void;

    public function counselorHasStudent(int $counselorId, int $studentId): bool;
}
