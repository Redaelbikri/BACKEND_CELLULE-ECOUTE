<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EducationalResourceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'category' => $this->category,
            'type' => $this->type,
            'description' => $this->description,
            'content' => $this->content,
            'reading_time' => $this->reading_time,
            'image_path' => $this->image_path,
            'external_url' => $this->external_url,
            'embed_url' => $this->embed_url,
            'video_url' => $this->video_url,
            'source_name' => $this->source_name,
            'practical_tips' => $this->practical_tips ?? [],
            'checklist' => $this->checklist ?? [],
            'is_published' => $this->is_published,
            'created_by' => $this->created_by,
            'creator' => $this->whenLoaded('creator', fn () => [
                'id' => $this->creator?->id,
                'name' => $this->creator?->name,
                'email' => $this->creator?->email,
            ]),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
