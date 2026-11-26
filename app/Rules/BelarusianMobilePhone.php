<?php

declare(strict_types=1);

namespace App\Rules;

use Illuminate\Contracts\Validation\ValidationRule;

class BelarusianMobilePhone implements ValidationRule
{
    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        if (!is_string($value) && !is_numeric($value)) {
            $fail('The :attribute must be a valid Belarusian mobile phone number.');
            return;
        }

        $digits = preg_replace('/[\s\-\(\)\+]/', '', (string) $value);
        if (str_starts_with($digits, '80') && strlen($digits) === 11) {
            $digits = '375' . substr($digits, 2);
        }

        if (!preg_match('/^375(25|29|33|44)\d{7}$/', $digits)) {
            $fail('The :attribute must be a valid Belarusian mobile phone number.');
        }
    }
}
