<?php

namespace App\Repositories\Eloquent;

use App\Models\SavedResource;
use App\Repositories\Interfaces\SavedResourceRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class SavedResourceRepository implements SavedResourceRepositoryInterface
{
    public function getForStudent(int $studentId): Collection
    {
        return SavedResource::query()
            ->with('resource')
            ->where('student_id', $studentId)
            ->latest()
            ->get();
    }

    public function find(int $studentId, int $resourceId): ?SavedResource
    {
        return SavedResource::query()
            ->where('student_id', $studentId)
            ->where('educational_resource_id', $resourceId)
            ->first();
    }

    public function save(int $studentId, int $resourceId): SavedResource
    {
        return SavedResource::query()->firstOrCreate([
            'student_id' => $studentId,
            'educational_resource_id' => $resourceId,
        ])->load('resource');
    }

    public function delete(SavedResource $savedResource): void
    {
        $savedResource->delete();
    }
}
