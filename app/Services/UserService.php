<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserService
{
    public function register ($request) : array
    {
        $user = User::create([
            "username" => $request['username'],
            "password" => Hash::make($request['password']),
            "email" => $request['email']
        ]);

        $user_role = Role::query()->where('name' , 'user')->first();
        $user -> assignRole($user_role);

        $user->load('roles');

        $user = User::query()->find($user['id']);
        $user = $this->appendRolesAndPermissions($user);
        $user['token'] = $user->createToken("token")->plainTextToken;

        $message = "User created successfully";

        return [
            'user' => $user,
            'message' => $message,
        ];
    }

    public function login($request): array
    {
        if(!Auth::attempt($credentials = $request->only(['username', 'password']))) {
            return [ 'message' => 'incorrect password', 'code' => 401];
        }   
        $user = $this->appendRolesAndPermissions($request->user());
        $user['access_token'] = auth()->setTTL(90)->claims(['type' => 'access'])->attempt($credentials);
        $user['refresh_token'] = auth()->setTTL(60 * 24 *2)->claims(['type' => 'refresh'])->attempt($credentials);

        return ['user' => $user , 'message' => "user logged in successfully" , 'code' => 200];
    }

    public function refresh(){
        auth()->invalidate();
        $data["access_token"] = auth()->setTTL(90)->claims(["type" => "access"])->tokenById(auth()->id());
        $data["refresh_token"] = auth()->setTTL(60 * 24 * 2)->claims(["type" => "refresh"])->tokenById(auth()->id());
        return [
            "token" => $data,
            "message" => "token successfully refreshed",
            "code" => 200
        ];
    }

    public function logout(): array
    {
        $user = Auth::user();

        if (!is_null($user)) {

            if (Auth::check()) {
                auth()->invalidate();
                $message = 'Logged out successfully!';
                $code = 200;
            } else {
                $message = 'Invalid token!';
                $code = 401;
            }
        } else {
            $message = 'User not found';
            $code = 404;
        }

        return ['user' => $user, 'message' => $message, 'code' => $code];
    }


    private function appendRolesAndPermissions($user)
    {
        $roles = [];
        foreach ($user -> roles as $role){
            $roles[] = $role -> name;
        }

        unset($user['roles']);
        $user['roles'] = $roles;

        $permissions = [];
        foreach ($user -> permissions as $permission){
            $permissions[] = $permission -> name;
        }

        unset($user['permissions']);
        $user['permissions'] = $permissions;

        return $user;

    }

}
