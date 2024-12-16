<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\UserService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Responses\Response;
use App\Models\Group;
use Illuminate\Http\JsonResponse;
use Throwable;

class UserController extends Controller
{

    private UserService $userService ;

    public function __construct(UserService $userService)
    {
       $this->userService = $userService ;
    }


    function indexPerGroup(Request $request,Group $group){
        $data = [];
        try {
            $data = $this->userService->indexPerGroup($request,$group);
            return Response::Success($data['users'], $data['message']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::Error($data, $message);
        }
    }

    public function getAllUsers(): JsonResponse
    {
        $data = [];
        try {
            $data = $this->userService->getAllUsers();
            return Response::Success($data['users'], $data['message'], withPagination:true);
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
