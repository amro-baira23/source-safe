<?php

namespace App\Http\Controllers;

use App\Http\Requests\GroupRequest;
use App\Http\Responses\Response;
use App\Services\GroupService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Throwable;

class GroupsController extends Controller
{
    private GroupService $GroupService ;

    public function __construct(GroupService $GroupService)
    {
       $this->GroupService = $GroupService ;
    }

    public function store_group(GroupRequest $request): JsonResponse
     {
        $data = [];
        try{
            $data = $this->GroupService->store_group($request);
            return Response::Success($data['group'],$data['message'] );

        }catch(Throwable $th){
            $message = $th->getMessage();
            return Response::Error($data,$message );
        }
     }


     public function index_group(): JsonResponse
     {
        $data = [];
        try{
            $data = $this->GroupService->index_group();
            return Response::Success($data['groups'],$data['message'],$data['code']);
        }catch(Throwable $th){
            $message = $th->getMessage();
            return Response::Error($data,$message );
        }
     }

     public function show_group($id): JsonResponse
     {
        $data = [];
        try{
            $data = $this->GroupService->show_group($id);
            return Response::Success($data['group'],$data['message'],$data['code']);
        }catch(Throwable $th){
            $message = $th->getMessage();
            return Response::Error($data,$message );
        }
     }
     public function update_group(GroupRequest $request,$id): JsonResponse
     {
        $data = [];
        try{
            $data = $this->GroupService->update_group($request,$id);
            return Response::Success($data['group'],$data['message'],$data['code']);
        }catch(Throwable $th){
            $message = $th->getMessage();
            return Response::Error($data,$message );
        }
     }
     public function joinGroup($groupId): JsonResponse
     {
        $data = [];
        try{
            $data = $this->GroupService->joinGroup($groupId);
            return Response::Success($data['message'],$data['code']);
        }catch(Throwable $th){
            $message = $th->getMessage();
            return Response::Error($data,$message );
        }
     }

     public function approveMember($groupId , $userId){
        $data = [];
        try{
            $data = $this->GroupService->approveMember($groupId , $userId);
            return Response::Success($data['message'],$data['code']);
        }catch(Throwable $th){
            $message = $th->getMessage();
            return Response::Error($data,$message );
        }
    }
}
