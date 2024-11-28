<?php
namespace App\Aspects;


use Illuminate\Support\Facades\Event;


interface ServiceInterface {
    public function doSomething();
}

// Aspect class
class EventAspect {
    public function before(ServiceInterface $service, callable $callback) {
        dump("before");
    }

    public function after(ServiceInterface $service, callable $callback) {
        dump("before");
    }
}

// Service implementation
class MyService implements ServiceInterface {
    public function doSomething() {
        // Method logic
    }
}

// Register the aspect
Event::listen('service.*', 'App\LoggingAspect');
