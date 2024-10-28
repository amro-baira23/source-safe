<?php

namespace App\Services;

use App\Models\Group;
use App\Models\Perm;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

class GroupService
{
public function store_group(Request $request): array
    {

        $group = Group::query()->create([
        'name'=>$request['name'],
        ]);

        $group->users()->attach(auth()->id(), ['role' => 'admin', 'approved' => true]);


        return [
            'group' => $group,
            'message' => 'Group created successfully',
            ];
    }

    public function index_group(): array
    {
        $groups = Group::query()->get()->all();

        if (!is_null($groups) && !empty($groups)) {
            $message = "all the groups";
            $code = 200;
        } else {
            $message = "no groups";
            $code = 404;
        }

        return ['groups' => $groups, 'message' => $message, 'code' => $code];
    }

    public function show_group($id): array
    {
        $group = Group::query()->find($id);

        if (!is_null($group) && !empty($group)) {
            $message = "successfuly";
            $code = 200;
        } else {
            $message = "no group";
            $code = 404;
        }

        return ['group' => $group, 'message' => $message, 'code' => $code];
    }


    public function update_group(Request $request ,$groupId): array
    {
        $group = Group::find($groupId);

        if (!is_null($group) && !empty($group)) {

            $group->update([
                'name'=>$request['name'],
            ]);
            $message = "success update";
            $code = 200;
        } else {
            $message = "no group";
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

        public function approveMember($groupId, $userId): array
        {
            $group = Group::findOrFail($groupId);

            $admin = $group->users()->where('user_id', auth()->id())->where('role', 'admin')->first();
            if (!$admin) {
                $message = "Only the admin can approve members";
                $code = 403;
            }

            $group->users()->updateExistingPivot($userId, ['approved' => true]);
                $message = "Member approved successfully";
                $code = 200;

            return ['message' => $message, 'code' => $code];
        }

}

