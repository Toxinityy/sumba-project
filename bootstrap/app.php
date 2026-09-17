<?php

use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'setlocale' => SetLocale::class,
        ]);

        // I2: spec §3 mandates Cloudflare in front of the app, so every
        // request Laravel sees actually came from a Cloudflare edge IP, not
        // the visitor. With no trusted proxies, $request->ip() returns that
        // edge IP for EVERY visitor, and throttle:5,1 on the contact route
        // keys on it — one script firing six POSTs locks out every genuine
        // enquirer for sixty seconds and loses their typed message. Trusting
        // '*' here is the standard Laravel guidance for an app that is only
        // ever reachable through a proxy — it is safe ONLY because the
        // origin itself must not be directly reachable (see the deploy
        // runbook step added for this: restrict the origin to Cloudflare's
        // published IP ranges). Cloudflare sets CF-Connecting-IP as well as
        // the standard X-Forwarded-For, and Laravel's default header set
        // already includes X-Forwarded-For.
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
