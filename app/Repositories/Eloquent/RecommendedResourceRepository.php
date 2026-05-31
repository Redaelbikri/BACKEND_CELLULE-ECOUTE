<?php

namespace App\Repositories\Eloquent;

use App\Models\RecommendedResource;
use App\Repositories\Interfaces\RecommendedResourceRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class RecommendedResourceRepository implements RecommendedResourceRepositoryInterface
{
    public function getForStudent(int $studentId): Collection
    {
        return RecommendedResource::query()
            ->with(['resource', 'counselor'])
            ->where('student_id', $studentId)
            ->latest()
            ->get();
    }

    public function getForCounselorStudent(int $counselorId, int $studentId): Collection
    {
        return RecommendedResource::query()
            ->with(['resource', 'student', 'counselor'])
            ->where('counselor_id', $counselorId)
            ->where('student_id', $studentId)
            ->latest()
            ->get();
    }

    public function recommend(int $studentId, int $counselorId, int $resourceId, ?string $note): RecommendedResource
    {
        $recommendation = RecommendedResource::query()->updateOrCreate([
            'student_id' => $studentId,
            'counselor_id' => $counselorId,
            'educational_resource_id' => $resourceId,
        ], [
            'note' => $note,
        ]);

        return $recommendation->load(['resource', 'student', 'counselor']);
    }
}
