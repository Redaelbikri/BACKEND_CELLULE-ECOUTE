<?php

namespace App\Repositories\Interfaces;

use App\Models\RecommendedResource;
use Illuminate\Database\Eloquent\Collection;

interface RecommendedResourceRepositoryInterface
{
    public function getForStudent(int $studentId): Collection;

    public function getForCounselorStudent(int $counselorId, int $studentId): Collection;

    public function recommend(int $studentId, int $counselorId, int $resourceId, ?string $note): RecommendedResource;
}
