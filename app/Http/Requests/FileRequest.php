<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class FileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->isMember($this->group);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

            return $this->getFileRules();


    }

    private function getFileRules(): array
    {
        return [
            "name" => ["required", "alpha_dash" , ],
            "group_id" => ["required", "integer" , Rule::exists("groups","id")],
            "path" => ["required", "file"],

        ];
    }

    private function editFileRules(): array
    {
        return [
            "name" => ["required", "alpha_dash"],
            "group_id" => ["required", "integer" , Rule::exists("groups","id")],
            "path" => ["required", "file"],
            "type" => ["required", "alpha"]

        ];
    }

}
