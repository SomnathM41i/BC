<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use App\Models\ExpenseCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransferToManagerRequest extends FormRequest
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
            'amount' => ['required', 'numeric', 'gt:0', 'decimal:0,2'],
            'category_id' => [
                'required',
                Rule::exists('expense_categories', 'id')
                    ->where('context', ExpenseCategory::CONTEXT_PROJECT),
            ],
            'manager_id' => [
                'required',
                Rule::exists('users', 'id')->where('role', UserRole::Manager->value),
            ],
            'transaction_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
