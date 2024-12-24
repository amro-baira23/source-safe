<?php

namespace App\Aspects;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class LoggingAspect extends Aspect
{

    public function before(Request $request, array $parameters = [])

    {
        Log::info(" Befor Request starting for route: " . $request->path());
    }


    public function after(Request $request, Response $response, array $parameters = [])
    {
        Log::info(" After Response completed for route: " . $request->path());
    }


}
