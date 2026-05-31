<?php

namespace App\Http\Requests\FollowUp;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateMoodJournalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mood' => ['required', Rule::in(['tres_bien', 'bien', 'moyen', 'stresse', 'fatigue', 'triste', 'anxieux'])],
            'note' => ['nullable', 'string', 'max:2000'],
            'mood_date' => ['nullable', 'date'],
        ];
    }
}
