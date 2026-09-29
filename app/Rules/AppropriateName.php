<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;
use Illuminate\Translation\PotentiallyTranslatedString;

class AppropriateName implements ValidationRule
{
    /**
     * @var array<string, string>
     */
    private const CHARACTER_SUBSTITUTIONS = [
        '0' => 'o',
        '1' => 'i',
        '2' => 'z',
        '3' => 'e',
        '4' => 'a',
        '5' => 's',
        '6' => 'g',
        '7' => 't',
        '8' => 'b',
        '9' => 'g',
        '@' => 'a',
        '$' => 's',
        '!' => 'i',
        '+' => 't',
    ];

    /**
     * Terms are compared after accents, substitutions and repeated letters are normalized.
     * Ambiguous Brazilian surnames such as Pinto are intentionally not included.
     *
     * @var list<string>
     */
    private const FORBIDDEN_WORDS = [
        'arombada',
        'arombado',
        'babaca',
        'boceta',
        'bosta',
        'buceta',
        'cacete',
        'caralho',
        'cu',
        'cuzao',
        'desgracada',
        'desgracado',
        'foda',
        'fodase',
        'foder',
        'idiota',
        'merda',
        'otaria',
        'otario',
        'piroca',
        'pora',
        'puta',
        'puto',
        'vagabunda',
        'vagabundo',
        'viada',
        'viado',
        'xoxota',
    ];

    /**
     * @var list<string>
     */
    private const FORBIDDEN_PHRASES = [
        'fdp',
        'filhodaputa',
        'pqp',
        'tomarnocu',
        'vaifoder',
        'vsf',
    ];

    /**
     * Short terms that may occur naturally inside legitimate names.
     *
     * @var list<string>
     */
    private const EXACT_MATCH_ONLY = [
        'cu',
        'pora',
        'puta',
        'puto',
    ];

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

        $normalizedName = strtr(Str::ascii(Str::lower($value)), self::CHARACTER_SUBSTITUTIONS);
        $tokens = preg_split('/\s+/u', trim($normalizedName), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $normalizedTokens = array_map($this->normalizeToken(...), $tokens);
        $compactName = implode('', $normalizedTokens);

        foreach ($normalizedTokens as $token) {
            if ($this->containsForbiddenWord($token)) {
                $fail('Informe um nome adequado para aparecer no ranking.');

                return;
            }
        }

        foreach (self::FORBIDDEN_PHRASES as $phrase) {
            if (str_contains($compactName, $phrase)) {
                $fail('Informe um nome adequado para aparecer no ranking.');

                return;
            }
        }
    }

    private function normalizeToken(string $token): string
    {
        $lettersOnly = preg_replace('/[^a-z]/', '', $token) ?? '';

        return preg_replace('/(.)\1+/i', '$1', $lettersOnly) ?? $lettersOnly;
    }

    private function containsForbiddenWord(string $token): bool
    {
        foreach (self::FORBIDDEN_WORDS as $word) {
            if ($token === $word) {
                return true;
            }

            if (! in_array($word, self::EXACT_MATCH_ONLY, true)
                && (str_starts_with($token, $word) || str_ends_with($token, $word))) {
                return true;
            }
        }

        return false;
    }
}
