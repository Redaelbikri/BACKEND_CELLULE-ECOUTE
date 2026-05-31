<?php

namespace App\Services\FollowUp;

use App\Enums\RoleEnum;
use App\Enums\UserStatusEnum;
use App\Models\MoodJournal;
use App\Models\User;
use App\Repositories\Interfaces\AppointmentRepositoryInterface;
use App\Repositories\Interfaces\MoodJournalRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpKernel\Exception\HttpException;

class MoodJournalService
{
    public function __construct(
        private readonly MoodJournalRepositoryInterface $moodJournalRepository,
        private readonly AppointmentRepositoryInterface $appointmentRepository,
        private readonly UserRepositoryInterface $userRepository
    ) {
    }

    public function studentIndex(User $user): Collection
    {
        $this->ensureRole($user, RoleEnum::STUDENT);

        return $this->moodJournalRepository->getForStudent($user->id);
    }

    public function store(User $user, array $data): MoodJournal
    {
        $this->ensureRole($user, RoleEnum::STUDENT);

        $date = $data['mood_date'] ?? Carbon::today()->format('Y-m-d');
        $existing = $this->moodJournalRepository->findByDate($user->id, $date);
        $payload = [
            'student_id' => $user->id,
            'mood' => $data['mood'],
            'note' => $data['note'] ?? null,
            'mood_date' => $date,
        ];

        return $existing
            ? $this->moodJournalRepository->update($existing, $payload)
            : $this->moodJournalRepository->create($payload);
    }

    public function update(User $user, int $id, array $data): MoodJournal
    {
        $this->ensureRole($user, RoleEnum::STUDENT);
        $entry = $this->findOwnedEntry($user, $id);

        return $this->moodJournalRepository->update($entry, $data);
    }

    public function counselorStudentIndex(User $user, int $studentId): Collection
    {
        $this->ensureFollowedStudent($user, $studentId);

        return $this->moodJournalRepository->getForStudent($studentId);
    }

    private function findOwnedEntry(User $user, int $id): MoodJournal
    {
        $entry = $this->moodJournalRepository->findById($id);

        if (! $entry || $entry->student_id !== $user->id) {
            throw new HttpException(404, 'Mood entry not found.');
        }

        return $entry;
    }

    private function ensureFollowedStudent(User $user, int $studentId): void
    {
        $this->ensureRole($user, RoleEnum::COUNSELOR);
        $student = $this->userRepository->findById($studentId);

        if (! $student || $student->role !== RoleEnum::STUDENT->value || $student->status !== UserStatusEnum::ACTIVE->value) {
            throw new HttpException(404, 'Student not found.');
        }

        if (! $this->appointmentRepository->hasStudentCounselorHistory($studentId, $user->id)) {
            throw new HttpException(403, 'You can only access followed students.');
        }
    }

    private function ensureRole(User $user, RoleEnum $role): void
    {
        if ($user->role !== $role->value) {
            throw new HttpException(403, 'You are not authorized to access this resource.');
        }
    }
}
