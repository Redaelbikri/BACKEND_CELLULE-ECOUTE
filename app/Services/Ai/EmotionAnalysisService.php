<?php

namespace App\Services\Ai;

use App\Enums\EmotionSourceTypeEnum;
use App\Enums\RoleEnum;
use App\Models\Appointment;
use App\Models\EmotionAnalysis;
use App\Models\User;
use App\Repositories\Interfaces\AppointmentRepositoryInterface;
use App\Repositories\Interfaces\EmotionAnalysisRepositoryInterface;
use App\Repositories\Interfaces\EventRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;
use ZipArchive;

class EmotionAnalysisService
{
    public function __construct(
        private readonly AiProviderService $aiProviderService,
        private readonly EmotionAnalysisRepositoryInterface $emotionAnalysisRepository,
        private readonly AppointmentRepositoryInterface $appointmentRepository,
        private readonly EventRepositoryInterface $eventRepository,
        private readonly UserRepositoryInterface $userRepository
    ) {
    }

    public function analyzeAppointmentReason(Appointment $appointment): EmotionAnalysis
    {
        return $this->storeAnalysis(
            $appointment->reason,
            [
                'student_id' => $appointment->student_id,
                'counselor_id' => $appointment->counselor_id,
                'appointment_id' => $appointment->id,
                'message_id' => null,
                'source_type' => EmotionSourceTypeEnum::APPOINTMENT_REASON->value,
            ],
        );
    }

    public function analyzeText(User $user, array $data): EmotionAnalysis
    {
        $appointment = null;

        if (! empty($data['appointment_id'])) {
            $appointment = $this->findAppointment((int) $data['appointment_id']);
        }

        $sourceType = (string) $data['source_type'];
        $studentId = $this->resolveStudentId($user, $data, $appointment);
        $counselorId = $this->resolveCounselorId($user, $data, $appointment);

        $this->validateContext($user, $studentId, $counselorId, $appointment, $sourceType);

        return $this->storeAnalysis(
            (string) $data['text'],
            [
                'student_id' => $studentId,
                'counselor_id' => $counselorId,
                'appointment_id' => $appointment?->id,
                'message_id' => $data['message_id'] ?? null,
                'source_type' => $sourceType,
            ],
        );
    }

    public function analyzeDocument(User $user, array $data): EmotionAnalysis
    {
        $appointment = null;

        if (! empty($data['appointment_id'])) {
            $appointment = $this->findAppointment((int) $data['appointment_id']);
        }

        $sourceType = (string) ($data['source_type'] ?? EmotionSourceTypeEnum::DOCUMENT->value);
        $studentId = $this->resolveStudentId($user, $data, $appointment);
        $counselorId = $this->resolveCounselorId($user, $data, $appointment);

        $this->validateContext($user, $studentId, $counselorId, $appointment, $sourceType);

        $text = $this->extractTextFromDocument($data['file']);

        if ($text === '') {
            throw new HttpException(422, "L'analyse IA des documents sera disponible apres extraction du texte.");
        }

        return $this->storeAnalysis(
            $text,
            [
                'student_id' => $studentId,
                'counselor_id' => $counselorId,
                'appointment_id' => $appointment?->id,
                'message_id' => $data['message_id'] ?? null,
                'source_type' => $sourceType,
            ],
        );
    }

    public function getStudentAnalyses(User $user, int $studentId): Collection
    {
        if ($user->role !== RoleEnum::COUNSELOR->value) {
            throw new HttpException(403, 'Only counselors can access student analyses.');
        }

        if (! $this->canCounselorAccessStudent($user->id, $studentId)) {
            throw new HttpException(403, 'You are not allowed to access this student.');
        }

        return $this->emotionAnalysisRepository->getByStudentId($studentId);
    }

    public function getAppointmentAnalysis(User $user, int $appointmentId): ?EmotionAnalysis
    {
        $appointment = $this->findAppointment($appointmentId);
        $this->authorizeCounselorOrAdminForAppointmentAnalysis($user, $appointment);

        return $this->emotionAnalysisRepository->findLatestByAppointmentId($appointmentId);
    }

    public function getMessageAnalysis(User $user, string $messageId): ?EmotionAnalysis
    {
        if ($user->role !== RoleEnum::COUNSELOR->value && $user->role !== RoleEnum::ADMIN->value) {
            throw new HttpException(403, 'Only counselors and admin can access message analyses.');
        }

        $analysis = $this->emotionAnalysisRepository->findLatestByMessageId($messageId);

        if (! $analysis) {
            return null;
        }

        if ($user->role === RoleEnum::COUNSELOR->value && ! $this->canCounselorReadAnalysis($user, $analysis)) {
            throw new HttpException(403, 'You are not authorized to access this analysis.');
        }

        return $analysis;
    }

    public function getCounselorAlerts(User $user): Collection
    {
        if ($user->role !== RoleEnum::COUNSELOR->value) {
            throw new HttpException(403, 'Only counselors can access emotion alerts.');
        }

        return $this->emotionAnalysisRepository->getAlertsForCounselor($user->id);
    }

    public function getAdminAnalyses(User $user): Collection
    {
        if ($user->role !== RoleEnum::ADMIN->value) {
            throw new HttpException(403, 'Only admin can access emotion analyses.');
        }

        return $this->emotionAnalysisRepository->getAll();
    }

    private function storeAnalysis(string $text, array $context): EmotionAnalysis
    {
        $analysis = $this->aiProviderService->analyze($text);

        return $this->emotionAnalysisRepository->create([
            'student_id' => $context['student_id'],
            'counselor_id' => $context['counselor_id'],
            'appointment_id' => $context['appointment_id'],
            'message_id' => $context['message_id'],
            'source_type' => $context['source_type'],
            'original_text' => $text,
            ...$analysis,
        ]);
    }

    private function resolveStudentId(User $user, array $data, ?Appointment $appointment): ?int
    {
        if ($appointment) {
            return $appointment->student_id;
        }

        if ($user->role === RoleEnum::STUDENT->value) {
            return $user->id;
        }

        return isset($data['student_id']) ? (int) $data['student_id'] : null;
    }

    private function resolveCounselorId(User $user, array $data, ?Appointment $appointment): ?int
    {
        if ($appointment) {
            return $appointment->counselor_id;
        }

        if ($user->role === RoleEnum::COUNSELOR->value) {
            return $user->id;
        }

        return isset($data['counselor_id']) ? (int) $data['counselor_id'] : null;
    }

    private function validateContext(
        User $user,
        ?int $studentId,
        ?int $counselorId,
        ?Appointment $appointment,
        string $sourceType
    ): void {
        if (! $studentId) {
            throw new HttpException(422, 'A student context is required for this analysis.');
        }

        $student = $this->userRepository->findById($studentId);

        if (! $student || $student->role !== RoleEnum::STUDENT->value) {
            throw new HttpException(422, 'Selected student is invalid.');
        }

        if ($counselorId) {
            $counselor = $this->userRepository->findById($counselorId);

            if (! $counselor || $counselor->role !== RoleEnum::COUNSELOR->value) {
                throw new HttpException(422, 'Selected counselor is invalid.');
            }
        }

        if ($user->role === RoleEnum::STUDENT->value) {
            if (! in_array($sourceType, [EmotionSourceTypeEnum::CHAT_MESSAGE->value, EmotionSourceTypeEnum::DOCUMENT->value], true)) {
                throw new HttpException(403, 'Students cannot access manual analysis.');
            }

            if ($studentId !== $user->id) {
                throw new HttpException(403, 'You are not allowed to analyze content for another student.');
            }

            if ($counselorId && ! $this->canCounselorAccessStudent($counselorId, $studentId)) {
                throw new HttpException(422, 'Selected counselor is not linked to this student.');
            }

            if ($appointment && $appointment->student_id !== $user->id) {
                throw new HttpException(403, 'You are not authorized to access this appointment.');
            }

            return;
        }

        if ($user->role === RoleEnum::COUNSELOR->value) {
            if ($counselorId && $counselorId !== $user->id) {
                throw new HttpException(403, 'You cannot create analyses for another counselor.');
            }

            if ($appointment) {
                $this->authorizeCounselorOrAdminForAppointmentAnalysis($user, $appointment);
                return;
            }

            if (! $this->canCounselorAccessStudent($user->id, $studentId)) {
                throw new HttpException(403, 'You are not allowed to access this student.');
            }

            return;
        }

        if ($appointment && $user->role !== RoleEnum::ADMIN->value) {
            throw new HttpException(403, 'You are not authorized to analyze this appointment.');
        }
    }

    private function findAppointment(int $appointmentId): Appointment
    {
        $appointment = $this->appointmentRepository->findById($appointmentId);

        if (! $appointment) {
            throw new HttpException(404, 'Appointment not found.');
        }

        return $appointment;
    }

    private function authorizeCounselorOrAdminForAppointmentAnalysis(User $user, Appointment $appointment): void
    {
        if ($user->role === RoleEnum::ADMIN->value) {
            return;
        }

        if ($user->role === RoleEnum::COUNSELOR->value && $appointment->counselor_id === $user->id) {
            return;
        }

        throw new HttpException(403, 'You are not authorized to access this analysis.');
    }

    private function canCounselorReadAnalysis(User $user, EmotionAnalysis $analysis): bool
    {
        if ($analysis->counselor_id && $analysis->counselor_id === $user->id) {
            return true;
        }

        return $this->canCounselorAccessStudent($user->id, (int) $analysis->student_id);
    }

    private function canCounselorAccessStudent(int $counselorId, int $studentId): bool
    {
        return $this->appointmentRepository->hasStudentCounselorHistory($studentId, $counselorId)
            || $this->eventRepository->counselorHasStudent($counselorId, $studentId);
    }

    private function extractTextFromDocument(UploadedFile $file): string
    {
        $extension = Str::lower((string) $file->getClientOriginalExtension());

        return match ($extension) {
            'txt' => $this->normalizeExtractedText((string) file_get_contents($file->getRealPath())),
            'docx' => $this->extractTextFromDocx($file),
            default => '',
        };
    }

    private function extractTextFromDocx(UploadedFile $file): string
    {
        $zip = new ZipArchive();

        if ($zip->open($file->getRealPath()) !== true) {
            return '';
        }

        $index = $zip->locateName('word/document.xml');

        if ($index === false) {
            $zip->close();

            return '';
        }

        $xml = $zip->getFromIndex($index);
        $zip->close();

        if (! is_string($xml) || $xml === '') {
            return '';
        }

        return $this->normalizeExtractedText(strip_tags(str_replace('</w:p>', PHP_EOL, $xml)));
    }

    private function normalizeExtractedText(string $text): string
    {
        $normalized = html_entity_decode($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $normalized = preg_replace('/[^\P{C}\t\r\n]+/u', ' ', $normalized) ?? $normalized;
        $normalized = preg_replace('/[ \t]+/', ' ', $normalized) ?? $normalized;
        $normalized = preg_replace('/\n{3,}/', PHP_EOL.PHP_EOL, $normalized) ?? $normalized;

        return trim(Str::limit($normalized, 15000, ''));
    }
}
