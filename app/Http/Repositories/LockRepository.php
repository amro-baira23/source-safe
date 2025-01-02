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
}
