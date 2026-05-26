<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeedbackResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'appointment_id' => $this->appointment_id,
            'author_id' => $this->author_id,
            'target_id' => $this->target_id,
            'author_role' => $this->author_role,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'appointment' => AppointmentResource::make($this->whenLoaded('appointment')),
            'author' => UserResource::make($this->whenLoaded('author')),
            'target' => UserResource::make($this->whenLoaded('target')),
        ];
    }
}
