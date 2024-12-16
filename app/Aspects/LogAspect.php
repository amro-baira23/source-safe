<?php

namespace App\Aspects;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class LogAspect extends Aspect
{
    public function before(Request $request, array $parameters = []): void
    {
        Log::info("Request starting for route: " . $request->path());
    }

    public function after(Request $request, Response $response, array $parameters = []): void
    {
        Log::info("Response completed for route: " . $request->path());
    }
}
