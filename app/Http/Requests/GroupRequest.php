<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class GroupRequest extends FormRequest
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
        return [
            'name' => 'required|string|max:255',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ];
    }

    public function after(){
        return [
            function (Validator $validator) {
                if ($this->groupdNameIsntUnique()) {
                    $validator->errors()->add(
                        'group_name',
                        'Group name should be unique for the user!'
                    );
                }
            }
        ];
    }

    public function groupdNameIsntUnique(){
        return false;
    }

}
