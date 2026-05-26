<?php

namespace App\Http\Resources;

use App\Models\EventRegistration;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class EventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $currentRegistration = $this->whenLoaded('registrations', fn () => $this->registrations->first());
        $registeredParticipantsCount = (int) ($this->registered_participants_count ?? 0);

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'event_date' => $this->event_date?->format('Y-m-d'),
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'location' => $this->location,
            'image_path' => $this->image_path,
            'image_url' => $this->image_path ? url(Storage::url($this->image_path)) : null,
            'counselor_id' => $this->counselor_id,
            'max_participants' => $this->max_participants,
            'status' => $this->status,
            'created_by' => $this->created_by,
            'registered_participants_count' => $registeredParticipantsCount,
            'is_full' => $registeredParticipantsCount >= (int) $this->max_participants,
            'current_user_registration' => $currentRegistration instanceof EventRegistration
                ? EventRegistrationResource::make($currentRegistration)->resolve()
                : null,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'counselor' => UserResource::make($this->whenLoaded('counselor')),
            'creator' => UserResource::make($this->whenLoaded('creator')),
            'registrations' => EventRegistrationResource::collection($this->whenLoaded('registrations')),
        ];
    }
}
