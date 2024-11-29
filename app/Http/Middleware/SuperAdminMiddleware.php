<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user_id = Auth::user()->id ;
        $user = User::find($user_id);

       if (Auth::check() && $user->hasRole('admin')){
           return $next($request);
       }else{
           return response()->json(['message' => 'Unauthorized, user must be super-admin to access this endpoint'], 403);
       }
    }
}
