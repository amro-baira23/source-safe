<?php

namespace App\Http\Controllers;

use App\Exports\UserOperationsExport;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\UserService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Responses\Response;
use App\Models\Group;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Excel as ExcelExcel;
use Maatwebsite\Excel\Facades\Excel;
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

    public function index(Request $request): JsonResponse
    {
        $data = [];
        try {
            $data = $this->userService->index($request);
            return Response::Success($data['users'], $data['message'], withPagination:true);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::Error($data, $message);
        }
    }


    public function getGroups(User $user): JsonResponse
    {
        $data = [];
        try {
            $data = $this->userService->getGroups($user);
            return Response::Success($data['groups'], $data['message']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::Error($data, $message);
        }
    }

    public function remove(User $user): JsonResponse
    {
        try {
            $data = $this->userService->remove($user);
            return Response::Success($data['user'], $data['message']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::Error([], $message);
        }
    }

    public function removeFromGroup(Request $request, Group $group ,User $user){
        $data = [];
        try{
            $data = $this->userService->removeFromGroup($group , $user);
            return Response::Success($data['message'],$data['code']);
        }catch(Throwable $th){
            $message = $th->getMessage();
            return Response::Error($data,$message , 403 );
        }
    }

    public function getOperations(Group $group, User $user): JsonResponse
    {
        try {
            $result = $this->userService->getOperations($user);
            return Response::Success($result['operations'], $result['message'], withPagination: true);
        } catch (Throwable $th) {
            return Response::Error([], $th->getMessage());
        }
    }


    public function getOperationsAsCSV(Group $group, User $user){
        return Excel::download(new UserOperationsExport($user),"$user->username.csv",ExcelExcel::CSV,[
            'Content-Type' => 'text/csv',
      ]);
    }

    public function getOperationsAsPDF(Group $group, User $user){
        return Excel::download(new UserOperationsExport($user),"$user->username.pdf",ExcelExcel::DOMPDF);
    }

    public function mostJoinedUser()
    {
        $user = User::withCount('groups')
            ->orderBy('groups_count', 'desc')
            ->first();

        return response()->json([
            'username' => $user->username,
            'group_count' => $user->groups_count,
        ]);
    }


}
