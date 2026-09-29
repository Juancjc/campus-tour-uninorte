<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ReportFilterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->is_admin === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
            'school' => ['nullable', 'string', 'max:180'],
            'profession_id' => ['nullable', 'integer', 'exists:professions,id'],
            'game_id' => ['nullable', 'integer', 'exists:games,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'authenticated' => ['nullable', 'boolean'],
        ];
    }
}
