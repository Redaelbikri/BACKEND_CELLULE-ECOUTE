<?php

namespace App\Http\Requests\FollowUp;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFollowUpPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'appointment_id' => ['nullable', 'integer', 'exists:appointments,id'],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'objective' => ['sometimes', 'required', 'string', 'max:4000'],
            'actions' => ['sometimes', 'required', 'string'],
            'next_step' => ['nullable', 'string', 'max:4000'],
            'next_follow_up_date' => ['nullable', 'date'],
            'status' => ['sometimes', 'required', Rule::in(['active', 'completed', 'paused'])],
        ];
    }
}
