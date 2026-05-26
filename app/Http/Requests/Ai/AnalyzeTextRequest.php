<?php

namespace App\Http\Requests\Ai;

use App\Enums\EmotionSourceTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AnalyzeTextRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'text' => ['required', 'string', 'max:15000'],
            'source_type' => ['required', Rule::in(EmotionSourceTypeEnum::values())],
            'student_id' => ['nullable', 'integer', 'exists:users,id'],
            'counselor_id' => ['nullable', 'integer', 'exists:users,id'],
            'appointment_id' => ['nullable', 'integer', 'exists:appointments,id'],
            'message_id' => ['nullable', 'string', 'max:191'],
        ];
    }
}
