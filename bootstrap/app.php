<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Actions\ImportEmployeesAction;
use App\Actions\FetchAttendanceAction;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withCommands([
        FetchAttendanceAction::class,
        ImportEmployeesAction::class
    ])
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
