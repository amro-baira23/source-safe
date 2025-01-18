<?php
namespace App\Http\Repositories;

use App\Models\Lock;

class LockRepository {

    public function firstCommit($file_model, $file_upload){
        return Lock::create([
            'user_id' => auth()->user()->id,
            'file_id' => $file_model->id,
            'status' => 0 ,
            'type' => $file_upload->extension(),
            'size' => $file_upload->getsize(),
            'Version_number' => 1,
        ]);
    }


    public function checkOut($file_model,$previous_lock,$file_upload){
        return Lock::create([
            'user_id' => auth()->user()->id,
            'file_id' => $file_model->id,
            'status' => 0 ,
            'type' => $file_upload?->extension() ?? $previous_lock->type,
            'size' => $file_upload?->getsize() ?? $previous_lock->size,
            'Version_number' => $previous_lock->Version_number + (is_null($file_upload) ? 0 : 1),
        ]);
    }
}
