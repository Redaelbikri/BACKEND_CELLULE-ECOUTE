<?php

namespace App\Http\Requests\Resource;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateResourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:120'],
            'type' => ['required', Rule::in(['article', 'conseil', 'exercice', 'video_link', 'guide'])],
            'description' => ['required', 'string', 'max:2000'],
            'content' => ['required', 'string'],
            'reading_time' => ['nullable', 'integer', 'min:1', 'max:120'],
            'image_path' => ['nullable', 'string', 'max:255'],
            'external_url' => ['nullable', 'url', 'max:2048'],
            'embed_url' => ['nullable', 'url', 'max:2048'],
            'video_url' => ['nullable', 'url', 'max:2048'],
            'source_name' => ['nullable', 'string', 'max:255'],
            'practical_tips' => ['nullable', 'array'],
            'practical_tips.*' => ['string', 'max:500'],
            'checklist' => ['nullable', 'array'],
            'checklist.*' => ['string', 'max:500'],
            'is_published' => ['nullable', 'boolean'],
        ];
    }
}
