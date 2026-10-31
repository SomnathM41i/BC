<?php

namespace App\Http\Requests\Admin;

use App\Support\WorkLevel;
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
            // Empty leaves the day as it is. The amount is never taken from the
            // form: it comes from the employee's normal day salary.
            'days.*.level' => ['nullable', 'in:'.implode(',', [...array_keys(WorkLevel::MULTIPLIERS), 'absent'])],
        ];
    }
}
