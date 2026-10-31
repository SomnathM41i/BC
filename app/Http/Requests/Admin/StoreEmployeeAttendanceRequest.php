<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isOwner() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'reference_date' => ['required', 'date'],
            'days' => ['required', 'array'],
            // Empty means "not marked" and is skipped.
            'days.*.status' => ['nullable', 'in:present,absent'],
            'days.*.amount' => ['nullable', 'numeric', 'gte:0', 'max:9999999', 'decimal:0,2'],
        ];
    }
}
