<?php

namespace App\Http\Controllers;

use App\Http\Requests\GroupRequest;
use App\Http\Requests\UpdateGroupRequest;
use App\Http\Resources\GroupResource;
use App\Http\Resources\GroupUserResource;
use App\Http\Responses\Response;
use App\Models\Group;
use App\Models\User;
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
            return Response::Success( new GroupUserResource($data['group']) , $data['message'] );

        }catch(Throwable $th){
            $message = $th->getMessage();
            return Response::Error($data,$message );
        }
     }


     public function index_group(Request $request): JsonResponse
     {
        $data = [];
        try{
            $data = $this->GroupService->index_group($request);
            return Response::Success($data['groups'],$data['message']);
        }catch(Throwable $th){
            $message = $th->getMessage();
            return Response::Error($data,$message );
        }
     }

     public function show_group(Group $group): JsonResponse
     {
        $data = [];
        try{
            $data = $this->GroupService->show_group($group);
            return Response::Success($data['group'],$data['message'],$data['code']);
        }catch(Throwable $th){
            $message = $th->getMessage();
            return Response::Error($data,$message );
        }
     }
     public function update_group(UpdateGroupRequest $request,$id): JsonResponse
     {
        $data = [];
        try{
            $data = $this->GroupService->update_group($request,$id);
            return Response::Success( new GroupUserResource($data['group']) , $data['message'],$data['code']);
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

     public function getJoinRequests(Group $group){
        $data = [];
        try{
            $data = $this->GroupService->getJoinRequests($group);
            return Response::Success($data["requests"],$data['message'],$data['code']);
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

     public function removeUserFromGroup(Request $request, Group $group ,User $user){
        $data = [];
        try{
            $data = $this->GroupService->removeUserFromGroup($group , $user);
            return Response::Success($data['message'],$data['code']);
        }catch(Throwable $th){
            $message = $th->getMessage();
            return Response::Error($data,$message , 403 );
        }
    }

    public function getAllGroups()
    {
        $files = $this->GroupService->getAllGroups();
        return GroupUserResource::collection($files);
    }

    public function deleteGroupWithFiles(Group $group): JsonResponse
    {
        try {
            $result = $this->GroupService->deleteGroupWithFiles($group);
            return Response::Success([], $result['message']);
        } catch (Throwable $th) {
            return Response::Error([], $th->getMessage());
        }
    }

    public function softDeleteGroup(Group $group): JsonResponse
    {
        try {
            $result = $this->GroupService->softDeleteGroup($group);
            return Response::Success([], $result['message']);
        } catch (Throwable $th) {
            return Response::Error([], $th->getMessage());
        }
    }
}
