<?php

namespace App\Http\Resources;

use App\Enums\RoleEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'counselor_id' => $this->counselor_id,
            'appointment_date' => $this->appointment_date?->format('Y-m-d'),
            'appointment_time' => $this->appointment_time,
            'type' => $this->type,
            'reason' => $this->reason,
            'status' => $this->status,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'student' => UserResource::make($this->whenLoaded('student')),
            'counselor' => UserResource::make($this->whenLoaded('counselor')),
            'feedbacks' => FeedbackResource::collection($this->whenLoaded('feedbacks')),
            'emotion_analysis' => $request->user()?->role === RoleEnum::STUDENT->value
                ? null
                : EmotionAnalysisResource::make($this->whenLoaded('latestEmotionAnalysis')),
        ];
    }
}
