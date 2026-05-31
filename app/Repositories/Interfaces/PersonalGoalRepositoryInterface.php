<?php

namespace App\Repositories\Interfaces;

use App\Models\PersonalGoal;
use Illuminate\Database\Eloquent\Collection;

interface PersonalGoalRepositoryInterface
{
    public function getForStudent(int $studentId): Collection;

    public function findById(int $id): ?PersonalGoal;

    public function create(array $data): PersonalGoal;

    public function update(PersonalGoal $goal, array $data): PersonalGoal;

    public function delete(PersonalGoal $goal): void;
}
