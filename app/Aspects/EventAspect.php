<?php

namespace App\Aspects;

use App\Jobs\SendNotificationToUsersJob;
use App\Models\File;
use App\Models\Group;
use App\Models\User;
use Closure;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EventAspect extends Aspect
{
    public function after(Request $request, Response $response, array $parameters = [])
    {   
        SendNotificationToUsersJob::dispatch(
            group: $request->group ?? "[not found]",
            user: $request->user(),
            file: session("data") ?? "",
            operation: $parameters[0]
        );
    }

}
