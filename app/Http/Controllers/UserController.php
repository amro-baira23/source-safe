<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\UserService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Responses\Response;
use Illuminate\Http\JsonResponse;
use Throwable;

class UserController extends Controller
{

    private UserService $userService ;

    public function __construct(UserService $userService)
    {
       $this->userService = $userService ;
    }


    function index(Request $request){
        $users = User::where("username","LIKE","%$request->username%")
            ->select(["id","username","email"])
            ->paginate(20);
        return UserResource::collection($users);
    }

    public function getAllUsers(): JsonResponse
    {
        $data = [];
        try {
            $data = $this->userService->getAllUsers();
            return Response::Success($data['users'], $data['message']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::Error($data, $message);
        }
    }

    public function deleteUser(User $user): JsonResponse
    {
        try {
            $data = $this->userService->deleteUser($user);
            return Response::Success($data['user'], $data['message']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::Error([], $message);
        }
    }

    public function getUserGroups(User $user): JsonResponse
    {
        $data = [];
        try {
            $data = $this->userService->getUserGroups($user);
            return Response::Success($data['groups'], $data['message']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::Error($data, $message);
        }
    }
}
