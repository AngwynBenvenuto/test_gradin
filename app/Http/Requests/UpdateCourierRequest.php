<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCourierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $courier = $this->route('courier');
        return [
            'code' => ['sometimes', 'required', 'string', 'max:30', 'regex:/^[A-Za-z0-9_-]+$/', Rule::unique('couriers', 'code')->ignore($courier->id)],
            'name' => ['sometimes', 'required', 'string', 'min:2', 'max:150'],
            'phone' => ['sometimes', 'required', 'string', 'regex:/^\+?[0-9]{9,15}$/', Rule::unique('couriers', 'phone')->ignore($courier->id)],
            'email' => ['sometimes', 'nullable', 'email:rfc', 'max:150', Rule::unique('couriers', 'email')->ignore($courier->id)],
            'level' => ['sometimes', 'required', 'integer', Rule::in([1, 2, 3, 4, 5])],
            'is_active' => ['sometimes', 'boolean'],
            'vehicle_type' => ['sometimes', 'nullable', 'string', Rule::in(['motorcycle', 'car', 'van', 'truck', 'bicycle'])],
            'license_plate' => ['sometimes', 'nullable', 'string', 'max:20'],
            'address' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'registered_at' => ['sometimes', 'date'],
        ];
    }
}
