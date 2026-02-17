<?php

use Illuminate\Foundation\Application;
use App\Helpers\Bootstrap\ExcepcionHandler;

$exceptionHandler = new ExcepcionHandler();
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )->withMiddleware()
    ->withExceptions($exceptionHandler)
    ->create();
