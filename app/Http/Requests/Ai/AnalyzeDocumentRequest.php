<?php

namespace App\Http\Requests\Ai;

use App\Enums\EmotionSourceTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AnalyzeDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:10240',
                'mimetypes:text/plain,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/msword',
                'mimes:txt,pdf,doc,docx',
            ],
            'source_type' => ['nullable', Rule::in(EmotionSourceTypeEnum::values())],
            'student_id' => ['nullable', 'integer', 'exists:users,id'],
            'counselor_id' => ['nullable', 'integer', 'exists:users,id'],
            'appointment_id' => ['nullable', 'integer', 'exists:appointments,id'],
            'message_id' => ['nullable', 'string', 'max:191'],
        ];
    }
}
