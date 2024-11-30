<?php

namespace App\Http\Middleware;

use App\Models\Group;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AdminGroupMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
         $group = $request->route('group');

         $user_id = auth()->user()->id;

         $isAdmin = $group->users()->where('user_id', $user_id)->first()->pivot->role;

         if ($isAdmin != 'admin') {
             return response()->json(['message' => 'Unauthorized. Only the group admin can access this resource.'], 403);
         }

         return $next($request);
     }

}
