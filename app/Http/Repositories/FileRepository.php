<?php
namespace App\Http\Repositories;

use App\Models\File;

class FileRepository {


    public function indexPerGroup($request,$group){
        return File::where("group_id",$group->id)
        ->when($request->name,function($query, $value) {
            return $query->where("name","like","%$value%");
        })
        ->paginate(15);
    }

    public function index($request){
        return File::when($request->name,function($query, $value) {
            return $query->where("name","like","%$value%");
        })
        ->paginate(15);
    }

    public function store(string $name,string $path,int $group_id,int $active = 0){
        return File::create([
            "name" => $name,
            "path" => $path,
            "group_id" => $group_id,
            "active" => $active,
        ]);
    }

    public function delete($file){
        return $file->delete();
    }
}