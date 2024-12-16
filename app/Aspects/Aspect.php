<?php

namespace App\Aspects;

use Illuminate\Http\Request;
use Closure;
use Symfony\Component\HttpFoundation\Response;

abstract class Aspect
{
    public function before(Request $request, array $parameters = [])
    {

    }

    public function after(Request $request, Response $response, array $parameters = [])
    {

    }

    public function onException(\Throwable $e, array $parameters = []): Response
    {
        $code = 500;
        if (method_exists($e,$method = "getStatusCode"))
            $code = $e->$method();
        return response()->json([
            'message' => $e->getMessage(),
        ], $code);
    }

    public function handle(Request $request, Closure $next, ...$parameters): Response
    {
        try {
            $this->before($request, $parameters);

            $response = $next($request);

            $this->after($request, $response, $parameters);

            return $response;

        } catch (\Throwable $e) {
            return $this->onException($e, $parameters);
        }
    }


}
