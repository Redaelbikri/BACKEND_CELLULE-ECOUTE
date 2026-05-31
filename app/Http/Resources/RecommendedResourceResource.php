<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecommendedResourceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'counselor_id' => $this->counselor_id,
            'resource_id' => $this->educational_resource_id,
            'note' => $this->note,
            'resource' => EducationalResourceResource::make($this->whenLoaded('resource')),
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
