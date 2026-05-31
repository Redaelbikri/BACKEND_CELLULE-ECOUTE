<?php

namespace App\Repositories\Eloquent;

use App\Models\MoodJournal;
use App\Repositories\Interfaces\MoodJournalRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class MoodJournalRepository implements MoodJournalRepositoryInterface
{
    public function getForStudent(int $studentId): Collection
    {
        return MoodJournal::query()
            ->where('student_id', $studentId)
            ->latest('mood_date')
            ->get();
    }

    public function findById(int $id): ?MoodJournal
    {
        return MoodJournal::query()->find($id);
    }

    public function findByDate(int $studentId, string $date): ?MoodJournal
    {
        return MoodJournal::query()
            ->where('student_id', $studentId)
            ->whereDate('mood_date', $date)
            ->first();
    }

    public function create(array $data): MoodJournal
    {
        return MoodJournal::query()->create($data);
    }

    public function update(MoodJournal $entry, array $data): MoodJournal
    {
        $entry->update($data);

        return $entry->refresh();
    }
}
