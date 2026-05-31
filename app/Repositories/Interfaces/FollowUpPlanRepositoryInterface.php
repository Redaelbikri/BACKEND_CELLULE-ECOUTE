<?php

namespace App\Repositories\Interfaces;

use App\Models\FollowUpPlan;
use Illuminate\Database\Eloquent\Collection;

interface FollowUpPlanRepositoryInterface
{
    public function getForStudent(int $studentId): Collection;

    public function getForCounselorStudent(int $counselorId, int $studentId): Collection;

    public function findById(int $id): ?FollowUpPlan;

    public function create(array $data): FollowUpPlan;

    public function update(FollowUpPlan $plan, array $data): FollowUpPlan;
}
