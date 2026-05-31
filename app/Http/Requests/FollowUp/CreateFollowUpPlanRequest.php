<?php

namespace App\Http\Requests\FollowUp;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateFollowUpPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'appointment_id' => ['nullable', 'integer', 'exists:appointments,id'],
            'title' => ['required', 'string', 'max:255'],
            'objective' => ['required', 'string', 'max:4000'],
            'actions' => ['required', 'string'],
            'next_step' => ['nullable', 'string', 'max:4000'],
            'next_follow_up_date' => ['nullable', 'date'],
            'status' => ['nullable', Rule::in(['active', 'completed', 'paused'])],
        ];
    }
}
