<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreGameEventsRequest extends FormRequest
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
            'events' => ['required', 'array', 'max:20'],
            'events.*.type' => ['required', 'string', 'in:game_opened,level_started,level_completed'],
            'events.*.level' => ['nullable', 'integer', 'min:1', 'max:100'],
            'events.*.payload' => ['nullable', 'array'],
        ];
    }
}
