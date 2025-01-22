<?php

namespace App\Services;

use App\Http\Repositories\UserRepository;
use App\Http\Resources\GroupResource;
use App\Http\Resources\operationsResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserService
{

    private UserRepository $userReporsitory;

    public function __construct()
    {
        $this->userReporsitory = new UserRepository();
    }
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

        $user = $this->appendRolesAndPermissions($user);

        $user['access_token'] = auth()->setTTL(90)->claims(['type' => 'access'])->tokenById($user->id);
        $user['refresh_token'] = auth()->setTTL(60 * 24 *2)->claims(['type' => 'refresh'])->tokenById($user->id);

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

    public function index($request): array
    {
        $users = $this->userReporsitory->index($request);
        return [
            'users' => UserResource::collection($users),
            'message' => " users successfully",
        ];
    }

    public function indexPerGroup($request,$group) {
        $users = $this->userReporsitory->indexPerGroup($request,$group);
        return [
            'users' => UserResource::collection($users),
            'message' => "'$group->name' group's users successfully",
        ];
    }
    public function remove(User $user): array
    {
        $user->delete();

        return [
            'user' => $user,
            'message' => 'User deleted successfully',
        ];
    }
    public function removeFromGroup($group, $user): array
    {
        $user_id = auth()->user()->id;
        $isAdmin = $group->users()->where('user_id', $user_id)->first()->pivot->role;

        if ($isAdmin != 'admin') {

            return ['message' => 'Unauthorized. Only the group admin can access this resource.', 'code' => 403];
        }

        if (!$group->users->contains($user->id)) {
            throw new Exception("user not found in group");
        }

        $userRole = $group->users()->where('user_id', $user->id)->first()->pivot->role;
        if ($userRole === 'admin') {
            throw new Exception("Cannot remove the admin of the group");
        }

        $group->users()->detach($user->id);

        return ['message' => 'User removed from the group successfully', 'code' => 200];
    }
    public function Groups(User $user): array
    {

        return [
            'groups' => GroupResource::collection($user->groups),
            'message' => 'Groups retrieved successfully for the user',
        ];
    }
    public function getOperations(User $user): array
    {
        $operations = $user->locks()
            ->whereHas("file",function ($query) {
                return $query->where("group_id",request("group")->id);
            })
            ->paginate(20);

        return [
            'operations' => operationsResource::collection($operations),
            'message' => 'All operations by this user.'
        ];
    }



}
