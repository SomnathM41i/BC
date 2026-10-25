<?php

namespace App\Http\Requests\Admin;

use App\Models\Contractor;
use App\Models\ExpenseCategory;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransferToContractorRequest extends FormRequest
{
    /**
     * Sentinel value submitted by the contractor dropdown when the owner wants to
     * create a brand new contractor on the spot instead of picking an existing one.
     */
    public const NEW_CONTRACTOR = '__new__';

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
            'contractor_id' => [
                'required',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if ($value === self::NEW_CONTRACTOR) {
                        return;
                    }

                    $exists = ctype_digit((string) $value)
                        && Contractor::query()
                            ->whereKey((int) $value)
                            ->where('is_active', true)
                            ->exists();

                    if (! $exists) {
                        $fail('The selected contractor is invalid.');
                    }
                },
            ],
            'new_contractor_name' => [
                'required_if:contractor_id,'.self::NEW_CONTRACTOR,
                'nullable',
                'string',
                'max:255',
                Rule::unique('contractors', 'name'),
            ],
            'new_contractor_phone' => [
                'required_if:contractor_id,'.self::NEW_CONTRACTOR,
                'nullable',
                'string',
                'max:30',
                'regex:/^[0-9+\-\s()]*$/',
                Rule::unique('contractors', 'phone'),
            ],
            'transaction_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'new_contractor_name.required_if' => 'Enter a name for the new contractor.',
            'new_contractor_name.unique' => 'A contractor with this name already exists.',
            'new_contractor_phone.required_if' => 'Enter a phone number for the new contractor.',
            'new_contractor_phone.unique' => 'A contractor with this phone number already exists.',
            'new_contractor_phone.regex' => 'The phone may only contain digits, spaces, and + - ( ) characters.',
        ];
    }
}
