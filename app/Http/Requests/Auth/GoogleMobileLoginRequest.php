<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class GoogleMobileLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'access_token' => ['nullable', 'string', 'required_without:id_token'],
            'id_token' => ['nullable', 'string', 'required_without:access_token'],
        ];
    }
}
