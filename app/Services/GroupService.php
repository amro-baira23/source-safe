<?php

namespace App\Services;

use App\Http\Resources\GroupResource;
use App\Http\Resources\GroupUserResource;
use App\Http\Resources\JoinRequestsResource;
use App\Http\Resources\operationsResource;
use App\Models\Group;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

class GroupService
{
public function store_group(Request $request): array
    {

        $group = Group::query()->create([
        'name'=>$request['name'],
        ]);

        $uesrs = User::all();

        $group->users()->attach(auth()->id(), ['role' => 'admin', 'approved' => true]);

        foreach ($request->user_ids as $user_id) {

            // if the user is admin -> ignored
            if ($user_id != auth()->id()) {
                $group->users()->attach($user_id, ['role' => 'member', 'approved' => true]);
            }
        }

        return [
            'group' => $group,
            'message' => 'Group created successfully',
            ];
    }

    public function index_group(Request $request): array
    {
        $groups = Group::whereHas("users",function ($query) use ($request){
            return $query->where("user_id",$request->user()->id);
        })->with(["users","users.roles"])->get();
        return ['groups' => GroupUserResource::collection($groups), 'message' => "groups retrieved successfully"];
    }

    public function show_group($group): array
    {
        $group->load("users");

        return ['group' => new GroupUserResource($group), 'message' => "retrieved successfully", 'code' => 200];
    }


    public function update_group(Request $request ,Group $group): array
    {
        //$group = $request->route('group');

        $user_id = auth()->user()->id;

        $isAdmin = $group->users()->where('user_id', $user_id)->first()->pivot->role;

            if ($isAdmin != 'admin') {

                return ['message' => 'Unauthorized. Only the group admin can access this resource.', 'code' => 403];
            }


        if (!is_null($group) && !empty($group)) {
            $group->update([
                'name'=>$request['name'],
            ]);
            if ($request->has('remove_user_ids')) {
                    foreach ($request->remove_user_ids as $userId) {
                        $userToRemove = $group->users->where('id', $userId)->first;
                        if ($userToRemove && $userToRemove->pivot->role !== 'admin') {
                            $group->users()->detach($userId);
                        }else{
                            $message = " Cannot remove the admin of the group , or the user not found in group ";
                            $code = 403;
                            return ['group' => $group, 'message' => $message, 'code' => $code];
                        }
                    }
            }

            if ($request->has('add_user_ids')) {
                foreach ($request->add_user_ids as $userId) {
                    if (!$group->users->contains($userId)) {
                        $group->users()->attach($userId, ['role' => 'member', 'approved' => true]);
                    }
                }
            }
            $group->refresh();

            $message = "success update";
            $code = 200;
        } else {
            $message = " group not found";
            $code = 404;
        }

        return ['group' => $group, 'message' => $message, 'code' => $code];
    }

    public function joinGroup($groupId): array
        {

            $group = Group::findOrFail($groupId);

            if ($group->users()->where('user_id', auth()->id())->exists()) {
                $message = "You are already a member of this group";
                $code = 400;
                return ['message' => $message, 'code' => $code];
            }

            $group->users()->attach(auth()->id(), ['role' => 'member', 'approved' => false]);

            $message = "Request to join group sent successfully";
            $code = 200;

            return ['message' => $message, 'code' => $code];
        }

        public function getJoinRequests($group) : array {

            $requests = $group->users()->where("approved",false)->get();

            $message = "Join requests been restored";
            $code = 200;

            return ['requests' => JoinRequestsResource::collection($requests),'message' => $message, 'code' => $code];

        }

        public function approveMember($groupId, $userId): array
        {
            $group = Group::findOrFail($groupId);

            $admin = $group->users()->where('user_id', auth()->id())->where('role', 'admin')->first();
            if (!$admin) {
                $message = "Only the admin can approve members";
                $code = 403;
                return ['message' => $message, 'code' => $code];
            }

            $group->users()->updateExistingPivot($userId, ['approved' => true]);
                $message = "Member approved successfully";
                $code = 200;

            return ['message' => $message, 'code' => $code];
        }


    public function getAllGroups()
    {

        return Group::with(["users","users.roles"])->get();
    }


    public function deleteGroupWithFiles(Group $group): array
    {
        DB::transaction(function () use ($group) {
            $group->files()->delete();
            $group->delete();
        });

        return [
            'group' => $group,
            'message' => 'Group and its files deleted successfully.'
        ];
    }


    public function softDeleteGroup(Group $group): array
    {
        $group->delete();

        return [
            'group' => $group,
            'message' => 'Group soft deleted successfully.'
        ];
    }


}

