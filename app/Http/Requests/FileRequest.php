<?php

namespace App\Http\Requests;

use App\Models\File;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

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
        if($this->is("*files"))
            return $this->postFileRules();
        if($this->is("*files/*"))
            return $this->editFileRules();
    }

    private function postFileRules(): array
    {
        return [
            "path" => ["required", "file"],
        ];
    }

    private function editFileRules(): array
    {
        return [
            "name" => ["alpha_num"],
            "type" => ["alpha"]
        ];
    }

    public function after(): array
    {
        if (!$this->path)
            return [];
        return [
            function (Validator $validator) {
                if ($this->fileNameIsntUnique()) {
                    $validator->errors()->add(
                        'file_name',
                        'File name should be unique!'
                    );
                }
            }
        ];
    }
    
    private function fileNameIsntUnique(){
        return File::where("group_id",$this->group->id)
                    ->where("name",$this->file("path")->getClientOriginalName())
                    ->exists();
    }

}
