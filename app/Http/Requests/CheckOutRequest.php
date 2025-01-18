<?php

namespace App\Http\Requests;

use App\Models\File;
use App\Models\Lock;
use App\Rules\FileIsActiveRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as ValidationValidator;

class CheckOutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $validator = Validator::make($this->only("file_id"),[
            'file_id' => ['required','integer',new FileIsActiveRule],
        ]);

        $validator->validate();
        $last_checkout = Lock::where("file_id",$this->file_id )
            ->where("status",1)->latest()->first();


        return $last_checkout?->user_id == auth()->id();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file_id' => 'required',
            'file' => 'nullable|file',
        ];
    }
    public function after(): array
    {
        if(is_null($this->file))
            return [];
        $file = File::find($this->file_id);
        $fileBaseName = pathinfo($file->name, PATHINFO_FILENAME);
        if (pathinfo($this->file->getClientOriginalName(), PATHINFO_FILENAME) !== $fileBaseName) {
            return [
                function (ValidationValidator $validator) {
                        $validator->errors()->add(
                            'file',
                            'File name should match the file name in the system'
                        );
                }
            ];
        }
        return [];
    }
    
}
