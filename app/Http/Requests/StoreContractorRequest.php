<?php

namespace App\Http\Requests;

use App\Models\ContractorType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContractorRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255', Rule::unique('contractors', 'name')],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s()]*$/', Rule::unique('contractors', 'phone')],
            'contractor_type_id' => [
                'required',
                Rule::exists('contractor_types', 'id')->where('is_active', true),
            ],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.unique' => 'A contractor with this name already exists.',
            'phone.unique' => 'A contractor with this phone number already exists.',
            'phone.regex' => 'The phone may only contain digits, spaces, and + - ( ) characters.',
        ];
    }
}
