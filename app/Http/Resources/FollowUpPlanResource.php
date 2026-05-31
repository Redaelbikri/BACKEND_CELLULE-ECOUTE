<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FollowUpPlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'counselor_id' => $this->counselor_id,
            'appointment_id' => $this->appointment_id,
            'title' => $this->title,
            'objective' => $this->objective,
            'actions' => $this->actions,
            'next_step' => $this->next_step,
            'next_follow_up_date' => $this->next_follow_up_date?->format('Y-m-d'),
            'status' => $this->status,
            'student' => $this->whenLoaded('student', fn () => [
                'id' => $this->student?->id,
                'name' => $this->student?->name,
                'email' => $this->student?->email,
            ]),
            'counselor' => $this->whenLoaded('counselor', fn () => [
                'id' => $this->counselor?->id,
                'name' => $this->counselor?->name,
                'email' => $this->counselor?->email,
            ]),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
