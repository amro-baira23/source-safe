<?php

namespace App\Aspects;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthRoleAspect extends Aspect
{

    public function before(Request $request, array $parameters = [])

    {

        $role = $parameters[0] ?? null;
        $group = $parameters['group'] ?? null;
        if (!$role) {
            return response()->json([
                'message' => 'Access Denied: Role is required',
            ], 403);
        }
    //dump($request->user()->isAdmin());
        if ($role === 'admin' && !$request->user()->isAdmin()) {
            // return response()->json([
            //     'message' => 'Unauthorized. user must be super-admin to access this endpoint',
            // ], 403);
            abort(403, 'Unauthorized. user must be super-admin to access this endpoint');
        }

        if ($role === 'member' && !$request->user()->isMember($group)) {
            // return response()->json([
            //     'message' => 'Unauthorized. Only group members can access this resource.',
            // ], 403);
            abort(403, 'Unauthorized. Only group members can access this resource.');
        }

        if ($role === 'adminGroup' && !$request->user()->isAdminGroup($group)) {
            // return response()->json([
            //     'message' => 'Unauthorized. Only group admin can access this resource.',
            // ], 403);
             abort(403, 'Unauthorized. Only group admin can access this resource.');
        }
    }

   


}
