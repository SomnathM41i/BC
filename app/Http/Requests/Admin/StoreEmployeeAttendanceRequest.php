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
     * The same endpoint saves either one day (attendance[]) or a whole salary
     * week (weekly[employee_id][date]), so the rules depend on the payload.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        if ($this->has('weekly')) {
            return [
                'weekly' => ['required', 'array'],
                'weekly.*' => ['array'],
                'weekly.*.*' => ['nullable', 'in:present,absent,half_day'],
                'weekly_employee_id' => ['nullable', 'integer', 'exists:employees,id'],
                'weekly_reference_date' => ['nullable', 'date'],
            ];
        }

        return [
            'attendance_date' => ['required', 'date'],
            'attendance' => ['required', 'array'],
            'attendance.*.employee_id' => ['required', 'integer', 'exists:employees,id'],
            // Empty means "not marked" and is skipped, so a fresh day can be
            // saved without accidentally marking everyone present.
            'attendance.*.status' => ['nullable', 'in:present,absent,half_day'],
        ];
    }
}
