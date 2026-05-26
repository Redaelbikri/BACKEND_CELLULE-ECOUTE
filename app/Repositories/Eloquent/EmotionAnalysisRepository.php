<?php

namespace App\Repositories\Eloquent;

use App\Enums\UrgencyLevelEnum;
use App\Models\EmotionAnalysis;
use App\Repositories\Interfaces\EmotionAnalysisRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EmotionAnalysisRepository implements EmotionAnalysisRepositoryInterface
{
    public function create(array $data): EmotionAnalysis
    {
        return EmotionAnalysis::query()
            ->create($data)
            ->load(['student', 'counselor', 'appointment']);
    }

    public function getByStudentId(int $studentId): Collection
    {
        return EmotionAnalysis::query()
            ->with(['student', 'counselor', 'appointment'])
            ->where('student_id', $studentId)
            ->latest()
            ->get();
    }

    public function findLatestByAppointmentId(int $appointmentId): ?EmotionAnalysis
    {
        return EmotionAnalysis::query()
            ->with(['student', 'counselor', 'appointment'])
            ->where('appointment_id', $appointmentId)
            ->latest()
            ->first();
    }

    public function findLatestByMessageId(string $messageId): ?EmotionAnalysis
    {
        return EmotionAnalysis::query()
            ->with(['student', 'counselor', 'appointment'])
            ->where('message_id', $messageId)
            ->latest()
            ->first();
    }

    public function getAlertsForCounselor(int $counselorId): Collection
    {
        return EmotionAnalysis::query()
            ->with(['student', 'appointment'])
            ->where('counselor_id', $counselorId)
            ->orderByRaw("
                CASE urgency_level
                    WHEN ? THEN 1
                    WHEN ? THEN 2
                    WHEN ? THEN 3
                    ELSE 4
                END
            ", [
                UrgencyLevelEnum::CRITICAL->value,
                UrgencyLevelEnum::HIGH->value,
                UrgencyLevelEnum::MEDIUM->value,
            ])
            ->latest()
            ->get();
    }

    public function getAll(): Collection
    {
        return EmotionAnalysis::query()
            ->with(['student', 'counselor', 'appointment'])
            ->latest()
            ->get();
    }
}
