<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{
    function login(AuthRequest $request){
        if(!Auth::attempt($request->only("username","password"))){
            return response(["error" => "incorrect password or username"] , 401);
        }
        $user = $request->user();
        $token = $user->createToken("user")->plainTextToken;
        return response(["success"=> true, "data" => $token, "message" => "welcome to source-safe!"]);
    }

    function register(AuthRequest $request){
        $user = User::create([
            "username" => $request->username,
            "password" => Hash::make($request->password),
            "email" => $request->email
        ]);
        return $user;
    }
}
