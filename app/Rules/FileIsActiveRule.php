<?php

namespace App\Rules;

use App\Models\File;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class FileIsActiveRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $file = File::where("id",$value)->first();
        if(is_null($file)){
            $fail("file_id with id $value doesn't exist");
        }

        if($file->active == 0){
            $fail("file is inactive");
        }

        if($file->status == 0){
            $fail("file is not reserved");
        }
    }

}
