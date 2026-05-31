<?php

namespace App\Repositories\Eloquent;

use App\Models\FollowUpPlan;
use App\Repositories\Interfaces\FollowUpPlanRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class FollowUpPlanRepository implements FollowUpPlanRepositoryInterface
{
    public function getForStudent(int $studentId): Collection
    {
        return FollowUpPlan::query()
            ->with(['counselor', 'appointment'])
            ->where('student_id', $studentId)
            ->latest()
            ->get();
    }

    public function getForCounselorStudent(int $counselorId, int $studentId): Collection
    {
        return FollowUpPlan::query()
            ->with(['student', 'counselor', 'appointment'])
            ->where('counselor_id', $counselorId)
            ->where('student_id', $studentId)
            ->latest()
            ->get();
    }

    public function findById(int $id): ?FollowUpPlan
    {
        return FollowUpPlan::query()
            ->with(['student', 'counselor', 'appointment'])
            ->find($id);
    }

    public function create(array $data): FollowUpPlan
    {
        return FollowUpPlan::query()->create($data)->load(['student', 'counselor', 'appointment']);
    }

    public function update(FollowUpPlan $plan, array $data): FollowUpPlan
    {
        $plan->update($data);

        return $plan->refresh()->load(['student', 'counselor', 'appointment']);
    }
}
