<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'agreed_amount' => ['required', 'numeric', 'gte:0', 'decimal:0,2'],
            'contractor_id' => [
                'nullable',
                Rule::exists('contractors', 'id')->where('is_active', true),
            ],
        ];
    }
}
