<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCourierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:30', 'regex:/^[A-Za-z0-9_-]+$/', Rule::unique('couriers', 'code')],
            'name' => ['required', 'string', 'min:2', 'max:150'],
            'phone' => ['required', 'string', 'regex:/^\+?[0-9]{9,15}$/', Rule::unique('couriers', 'phone')],
            'email' => ['nullable', 'email:rfc', 'max:150', Rule::unique('couriers', 'email')],
            'level' => ['required', 'integer', Rule::in([1, 2, 3, 4, 5])],
            'is_active' => ['sometimes', 'boolean'],
            'vehicle_type' => ['nullable', 'string', Rule::in(['motorcycle', 'car', 'van', 'truck', 'bicycle'])],
            'license_plate' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'registered_at' => ['sometimes', 'date'],
        ];
    }
}
