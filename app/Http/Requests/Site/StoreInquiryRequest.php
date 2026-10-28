<?php

namespace App\Http\Requests\Site;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'phone' => ['required', 'string', 'regex:/^(\+?91[\s-]?)?[6-9]\d{9}$/'],
            'location' => ['nullable', 'string', 'max:120'],
            'budget' => ['nullable', 'string', 'max:60'],
            'email' => ['nullable', 'email', 'max:190'],
            'service' => ['nullable', 'string', Rule::in([...config('site.services'), 'other'])],
            'message' => ['required', 'string', 'min:10', 'max:3000'],
            // Honeypot — accepted here and checked in the controller.
            'company_website' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => __('site.contact.validation.name_required'),
            'name.min' => __('site.contact.validation.name_required'),
            'phone.required' => __('site.contact.validation.phone_required'),
            'phone.regex' => __('site.contact.validation.phone_invalid'),
            'location.max' => __('site.contact.validation.location_max'),
            'budget.max' => __('site.contact.validation.budget_max'),
            'email.email' => __('site.contact.validation.email_invalid'),
            'service.in' => __('site.contact.validation.service_invalid'),
            'message.required' => __('site.contact.validation.message_required'),
            'message.min' => __('site.contact.validation.message_min'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => __('site.contact.form.name'),
            'phone' => __('site.contact.form.phone'),
            'location' => __('site.contact.form.location'),
            'budget' => __('site.contact.form.budget'),
            'email' => __('site.contact.form.email'),
            'service' => __('site.contact.form.service'),
            'message' => __('site.contact.form.message'),
        ];
    }
}
