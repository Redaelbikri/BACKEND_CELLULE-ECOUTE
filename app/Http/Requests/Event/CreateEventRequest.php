<?php

namespace App\Http\Requests\Event;

use App\Enums\EventStatusEnum;
use App\Enums\EventTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:3000'],
            'type' => ['required', Rule::in(EventTypeEnum::values())],
            'event_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'location' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'counselor_id' => ['nullable', 'integer', 'exists:users,id'],
            'max_participants' => ['required', 'integer', 'min:1', 'max:1000'],
            'status' => ['nullable', Rule::in(EventStatusEnum::values())],
        ];
    }
}
