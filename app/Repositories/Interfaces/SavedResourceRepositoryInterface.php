<?php

namespace App\Repositories\Interfaces;

use App\Models\SavedResource;
use Illuminate\Database\Eloquent\Collection;

interface SavedResourceRepositoryInterface
{
    public function getForStudent(int $studentId): Collection;

    public function find(int $studentId, int $resourceId): ?SavedResource;

    public function save(int $studentId, int $resourceId): SavedResource;

    public function delete(SavedResource $savedResource): void;
}
