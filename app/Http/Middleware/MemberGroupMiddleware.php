<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Group;

class MemberGroupMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
         $group = $request->route("group");

         $user_id = auth()->user()->id;

         $userRole = $group->users()->where('user_id', $user_id)->first()->pivot->role;

         if ($userRole === 'member' || $userRole === 'admin') {
            return $next($request);
         }

         return response()->json(['message' => 'Unauthorized. Only the group members can access this resource.'], 403);

    }
}
