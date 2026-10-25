<?php

use App\Http\Middleware\PastikanPeran;
use App\Http\Middleware\PastikanTerverifikasi;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Nama singkat middleware, dipakai di routes/web.php: ->middleware('peran:admin')
        $middleware->alias([
            'peran' => PastikanPeran::class,
            'terverifikasi' => PastikanTerverifikasi::class,
        ]);

        // Belum login diarahkan ke halaman login; sudah login diarahkan ke dashboard
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();