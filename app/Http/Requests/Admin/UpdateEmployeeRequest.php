<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isOwner() ?? false;
    }

    public function rules(): array
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        return [
            'full_name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:20'],
            'employee_type_id' => ['required', 'integer', 'exists:employee_types,id'],
            'weekly_salary_payment_day' => ['required', 'string', 'in:'.implode(',', $days)],
            'joining_date' => ['required', 'date'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
