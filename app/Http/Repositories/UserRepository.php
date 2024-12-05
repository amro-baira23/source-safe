<?php
namespace App\Http\Repositories;

use App\Models\User;

class UserRepository {


    public function indexPerGroup($request,$group){
        $users = User::where("username","LIKE","%$request->username%")
        ->whereHas("groups", function ($query) use ($group){
            return $query->where("group_id",$group->id);
        })
        ->select(["id","username","email"])
        ->paginate(20);
        return $users;
    }

    public function index($request){
    }

    public function store(string $name,string $path,int $group_id,int $active = 0){
    }

    public function delete($file){
    }
}
