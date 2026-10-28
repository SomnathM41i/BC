<?php

namespace App\Http\Requests\Admin;

use App\Rules\IndianMobile;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectClientRequest extends FormRequest
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
            'client_name' => ['required', 'string', 'max:255'],
            'client_mobile' => ['required', 'string', 'max:20', new IndianMobile],
        ];
    }
}
