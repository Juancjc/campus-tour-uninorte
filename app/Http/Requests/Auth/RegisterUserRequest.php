<?php

namespace App\Http\Requests\Auth;

use App\Models\Profession;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class RegisterUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)],
            'school_name' => ['required', 'string', 'max:180'],
            'profession_id' => ['required', 'integer', Rule::exists(Profession::class, 'id')->where('active', true)],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];
    }

    public function messages(): array
    {
        return [
            'school_name.required' => 'Informe a instituição onde você estuda.',
            'profession_id.required' => 'Escolha a profissão ou opção que mais combina com você.',
            'profession_id.exists' => 'Escolha uma profissão válida.',
        ];
    }
}
