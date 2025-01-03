<?php

namespace App\Aspects;

use App\Jobs\TrackFileChanges;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class TracingAspect extends Aspect
{



    public function after(Request $request, Response $response, array $parameters = [])
    {
        TrackFileChanges::dispatchSync();
    }


}
