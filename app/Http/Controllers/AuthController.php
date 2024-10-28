<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Responses\Response;
use Illuminate\Http\JsonResponse;
use Throwable;


class AuthController extends Controller
{
    private UserService $userService ;

     public function __construct(UserService $userService)
     {
        $this->userService = $userService ;
     }
     public function register(AuthRequest $request): JsonResponse
     {
        $data = [];
        try{
            $data = $this->userService->register($request->validated());
            return Response::Success($data['user'],$data['message'] );

        }catch(Throwable $th){
            $message = $th->getMessage();
            return Response::Error($data,$message );
        }
     }

     public function login(AuthRequest $request): JsonResponse
     {
         $data = [];
         try{
             $data = $this->userService->login($request);
             return Response::Success($data['user'],$data['message'],$data['code']);
         }catch(Throwable $th){
             $message = $th->getMessage();
             return Response::Error($data,$message );
         }
     }

    public function logout(): JsonResponse
    {
        $data = [];
        try{
            $data = $this->userService->logout();
            return Response::Success($data['user'],$data['message'],$data['code']);
        }catch(Throwable $th){
            $message = $th->getMessage();
            return Response::Error($data,$message );
        }
    }


}
