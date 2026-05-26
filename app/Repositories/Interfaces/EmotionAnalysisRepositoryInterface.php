<?php

namespace App\Repositories\Interfaces;

use App\Models\EmotionAnalysis;
use Illuminate\Database\Eloquent\Collection;

interface EmotionAnalysisRepositoryInterface
{
    public function create(array $data): EmotionAnalysis;

    public function getByStudentId(int $studentId): Collection;

    public function findLatestByAppointmentId(int $appointmentId): ?EmotionAnalysis;

    public function findLatestByMessageId(string $messageId): ?EmotionAnalysis;

    public function getAlertsForCounselor(int $counselorId): Collection;

    public function getAll(): Collection;
}
