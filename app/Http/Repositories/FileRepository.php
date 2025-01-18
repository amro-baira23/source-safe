<?php
namespace App\Http\Repositories;

use App\Models\File;
use App\Models\Group;

class FileRepository {


    public function indexPerGroup($request,$group) {
        return File::where("group_id",$group->id)
        ->when($request->name,function($query, $value) {
            return $query->where("name","like","%$value%");
        })->paginate(15);
    }

    public function index($request){
        return File::when($request->name,function($query, $value) {
            return $query->where("name","like","%$value%");
        })->paginate(15);
    }

   
    public function storeActivated(string $name,Group $group){
        return $this->store($name, $group, active: 1);
    }

    public function store(string $name,Group $group, $active = 0){
        return File::create([
            "name" => $name,
            "group_id" => $group->id,
            "active" => $active,
        ]);
    }
    
    
    public function delete($file){
        return $file->delete();
    }
}
