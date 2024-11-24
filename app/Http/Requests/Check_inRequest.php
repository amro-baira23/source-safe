<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Check_inRequest extends FormRequest
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
            'file_ids' => 'required|array|min:1',
            'file_ids.*' => 'integer|exists:files,id',
            'versions' => 'required|array',
            'versions.*' => 'integer|min:1',

           // 'version' => 'required|integer|exists:locks,Version_number',

        ];
    }
}
