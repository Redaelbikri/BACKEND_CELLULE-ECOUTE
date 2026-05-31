<?php

namespace App\Repositories\Interfaces;

use App\Models\MoodJournal;
use Illuminate\Database\Eloquent\Collection;

interface MoodJournalRepositoryInterface
{
    public function getForStudent(int $studentId): Collection;

    public function findById(int $id): ?MoodJournal;

    public function findByDate(int $studentId, string $date): ?MoodJournal;

    public function create(array $data): MoodJournal;

    public function update(MoodJournal $entry, array $data): MoodJournal;
}
