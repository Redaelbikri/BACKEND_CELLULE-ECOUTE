<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmotionAnalysisResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'counselor_id' => $this->counselor_id,
            'appointment_id' => $this->appointment_id,
            'message_id' => $this->message_id,
            'source_type' => $this->source_type,
            'original_text' => $this->original_text,
            'title' => $this->title ?: $this->emotional_state,
            'emotional_state' => $this->emotional_state,
            'main_emotion' => $this->main_emotion ?: $this->emotion,
            'emotion' => $this->main_emotion ?: $this->emotion,
            'sentiment' => $this->sentiment,
            'urgency_level' => $this->urgency_level,
            'problem_type' => $this->problem_type,
            'summary' => $this->summary,
            'key_signals' => $this->key_signals ?? [],
            'possible_causes' => $this->possible_causes ?? [],
            'recommendation' => $this->recommendation,
            'suggested_response' => $this->suggested_response,
            'suggested_action' => $this->suggested_action,
            'risk_level_explanation' => $this->risk_level_explanation,
            'confidence_score' => $this->confidence_score,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'student' => UserResource::make($this->whenLoaded('student')),
            'counselor' => UserResource::make($this->whenLoaded('counselor')),
            'appointment' => $this->whenLoaded('appointment', function () {
                return [
                    'id' => $this->appointment?->id,
                    'appointment_date' => $this->appointment?->appointment_date?->format('Y-m-d'),
                    'appointment_time' => $this->appointment?->appointment_time,
                    'status' => $this->appointment?->status,
                    'type' => $this->appointment?->type,
                ];
            }),
        ];
    }
}
