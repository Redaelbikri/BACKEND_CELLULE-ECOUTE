<?php

namespace App\Repositories\Eloquent;

use App\Models\PersonalGoal;
use App\Repositories\Interfaces\PersonalGoalRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class PersonalGoalRepository implements PersonalGoalRepositoryInterface
{
    public function getForStudent(int $studentId): Collection
    {
        return PersonalGoal::query()
            ->with('suggester')
            ->where('student_id', $studentId)
            ->latest()
            ->get();
    }

    public function findById(int $id): ?PersonalGoal
    {
        return PersonalGoal::query()->with('suggester')->find($id);
    }

    public function create(array $data): PersonalGoal
    {
        return PersonalGoal::query()->create($data)->load('suggester');
    }

    public function update(PersonalGoal $goal, array $data): PersonalGoal
    {
        $goal->update($data);

        return $goal->refresh()->load('suggester');
    }

    public function delete(PersonalGoal $goal): void
    {
        $goal->delete();
    }
}
