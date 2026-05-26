<?php

namespace App\Http\Requests\Appointment;

use App\Enums\AppointmentStatusEnum;
use App\Enums\AppointmentTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'counselor_id' => ['sometimes', 'required', 'integer', 'exists:users,id'],
            'appointment_date' => ['sometimes', 'required', 'date_format:Y-m-d'],
            'appointment_time' => ['sometimes', 'required', 'date_format:H:i'],
            'type' => ['sometimes', 'required', Rule::in(AppointmentTypeEnum::values())],
            'reason' => ['sometimes', 'required', 'string', 'max:1000'],
            'status' => ['sometimes', 'required', Rule::in(AppointmentStatusEnum::values())],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
