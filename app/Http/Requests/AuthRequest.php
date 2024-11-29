<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AuthRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

        if ($this->is("*login"))
            return $this->getLoginRules();
        if ($this->is("*register"))
            return $this->getRegisterRules();
        return [];
    }

    private function getLoginRules(): array
    {
        return [
            "username" => ["required", Rule::exists("users","username")],
            "password" => ["required"]
        ];
    }

    private function getRegisterRules(): array
    {
        return [
            "username" => ["required","alpha_dash", Rule::unique("users", "username")],
            "password" => ["required", "confirmed", Password::min(8)->numbers()],
            "password_confirmation" => ["required", Password::min(8)->numbers()],
            "email" => ["required", "email",Rule::unique("users", "username")],
        ];
    }
}
