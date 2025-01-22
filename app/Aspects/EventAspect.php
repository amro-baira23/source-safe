<?php

namespace App\Aspects;

use App\Jobs\SendNotificationToUsersJob;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;

class EventAspect extends Aspect
{
    public function after(Request $request, Response $response, array $parameters = [])
    {   
        if ($response->getStatusCode() == 200){
            SendNotificationToUsersJob::dispatch(
                group: $request->group ?? "[not found]",
                user: $request->user(),
                file: session("data") ?? "",
                operation: $parameters[0]
            );
        }
    }

}
