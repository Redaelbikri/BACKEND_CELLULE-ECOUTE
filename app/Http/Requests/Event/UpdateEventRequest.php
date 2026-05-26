<?php

namespace App\Http\Requests\Event;

use App\Enums\EventStatusEnum;
use App\Enums\EventTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string', 'max:3000'],
            'type' => ['sometimes', 'required', Rule::in(EventTypeEnum::values())],
            'event_date' => ['sometimes', 'required', 'date_format:Y-m-d'],
            'start_time' => ['sometimes', 'required', 'date_format:H:i'],
            'end_time' => ['sometimes', 'required', 'date_format:H:i'],
            'location' => ['sometimes', 'required', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_image' => ['sometimes', 'boolean'],
            'counselor_id' => ['nullable', 'integer', 'exists:users,id'],
            'max_participants' => ['sometimes', 'required', 'integer', 'min:1', 'max:1000'],
            'status' => ['sometimes', 'required', Rule::in(EventStatusEnum::values())],
        ];
    }
}
