<?php

namespace App\Http\Middleware;

use App\Aspects\Aspect;
use App\Aspects\AuthRoleAspect;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AspectMiddleware
{

    protected AuthRoleAspect $aspect;

    public function __construct(Aspect $aspect)
    {
        $this->aspect = $aspect;
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$parameters): Response
    {
        return $this->aspect->handle($request, $next, ...$parameters);
    }
}
