<?php

namespace App\Aspects;

use App\Http\Controllers\AuthController;
use Okapi\Aop\Attributes\After;
use Okapi\Aop\Attributes\Aspect ;
use Okapi\Aop\Attributes\Before ;
use Okapi\Aop\Invocation\AfterMethodInvocation;
use Okapi\Aop\Invocation\BeforeMethodInvocation;

/**
 * Monitor aspect
 */
#[Aspect]
class ExampleAspect 
{



    #[Before(
        class: AuthController::class,
        method: "*"
    )]
    public function beforeMethodExecution(BeforeMethodInvocation $invocation)
    {
        echo "Example Aspect";
        dump($invocation->getAdviceType(),$invocation->getMethodName());

    }

    // #[After(
    //     class: AuthController::class,
    //     method: "*"
    // )]
    // public function afterMethodExecution(AfterMethodInvocation $invocation)
    // {
    //     echo "Example Aspect";
    //     dump($invocation->getAdviceType(),$invocation->getMethodName());
    // }

    
}