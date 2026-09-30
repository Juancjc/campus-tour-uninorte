<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CompleteGameSessionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'duration_ms' => ['required', 'integer', 'min:1000', 'max:3600000'],
            'payload' => ['present', 'array'],
            'payload.level' => ['sometimes', 'integer', 'between:1,5'],
            'payload.commands' => ['sometimes', 'array', 'max:30'],
            'payload.commands.*' => ['string', 'in:up,down,left,right,jump'],
        ];
    }
}
