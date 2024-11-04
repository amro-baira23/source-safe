<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    function index(Request $request){
        $users = User::where("username","LIKE","%$request->username%")
            ->select(["id","username","email"])
            ->paginate(20);
        return UserResource::collection($users);
    }
}
