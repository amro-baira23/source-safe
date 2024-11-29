<?php

namespace App\Services;

use App\Http\Resources\UserResource;
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
        $user = User::query()->where('username',$request['username'])->first();

        if(!is_null($user)){

            if(!Auth::attempt($request->only(['username', 'password']))) {
                $message = 'username and password are not in our records';
                $code = 401;
            }else{
                $user = $this->appendRolesAndPermissions($user);
                $user['token'] = $user->createToken('token')->plainTextToken;
                $message = 'user logged in successfully!';
                $code = 200;
            }
        }else{
            $message = 'user not found';
            $code = 404;
        }

        return ['user' => $user , 'message' => $message , 'code' => $code];
    }

    public function logout(): array
    {
        $user = Auth::user();

        if (!is_null($user)) {

            if (Auth::check()) {
                Auth::user()->currentAccessToken()->delete();
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

    public function getAllUsers(): array
    {
        $users = User::with('roles')->get();

        return [
            'users' => UserResource::collection($users),
            'message' => 'All users retrieved successfully',
        ];
    }

    public function deleteUser(User $user): array
    {
        $user->delete();

        return [
            'user' => $user,
            'message' => 'User deleted successfully',
        ];
    }

    public function getUserGroups(User $user): array
    {

        return [
            'groups' => $user->groups,
            'message' => 'Groups retrieved successfully for the user',
        ];
    }

}
