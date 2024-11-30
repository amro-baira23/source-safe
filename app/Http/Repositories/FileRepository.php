<?php
namespace App\Http\Repositories;

use App\Models\File;

class FileRepository {


    public function indexPerGroup($request,$group){
        return File::where("group_id",$group->id)->get();
    }
}