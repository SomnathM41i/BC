<?php

namespace App\Rules;

use App\Support\PhoneNumber;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IndianMobile implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (PhoneNumber::normalize(is_string($value) ? $value : null) === null) {
            $fail('The :attribute must be a valid 10-digit Indian mobile number.');
        }
    }
}
