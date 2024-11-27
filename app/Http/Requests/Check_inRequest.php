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

        'files' => 'required|array',
        'files.*.file_id' => 'required|integer|exists:files,id',
        'files.*.version' => 'required|integer',


           // 'version' => 'required|integer|exists:locks,Version_number',

        ];
    }
}
