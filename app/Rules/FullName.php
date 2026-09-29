<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class FullName implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            return;
        }

        $parts = preg_split('/\s+/u', trim($value), -1, PREG_SPLIT_NO_EMPTY);

        if ($parts === false || count($parts) < 2) {
            $fail('Informe seu nome completo, com nome e sobrenome.');

            return;
        }

        foreach ($parts as $part) {
            if (preg_match('/\A\p{Latin}+(?:[\'’\-]\p{Latin}+)*\z/u', $part) !== 1
                || preg_match_all('/\p{Latin}/u', $part) < 2) {
                $fail('Use apenas letras, espaços, hífens e apóstrofos no nome completo.');

                return;
            }
        }
    }
}
