<?php

// tests/Feature/SetLocaleMiddlewareTest.php
//
// Exercises SetLocale::handle() directly, rather than through routing, so
// this test genuinely proves the middleware's own contract: it rejects an
// unsupported locale itself, regardless of whether a route ever happens to
// exist under that prefix. (tests/Pest.php binds TestCase to Feature only,
// so this lives here rather than in Unit.)

use App\Http\Middleware\SetLocale;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

function requestWithLocale(string $locale): Request
{
    $request = Request::create('/'.$locale.'/whatever', 'GET');
    $route = new Route('GET', '/{locale}/{any}', fn () => null);
    $route->bind($request);
    $route->setParameter('locale', $locale);
    $request->setRouteResolver(fn () => $route);

    return $request;
}

it('aborts with a 404 when the route locale is not supported', function () {
    $request = requestWithLocale('fr');
    $middleware = new SetLocale;

    expect(fn () => $middleware->handle($request, fn ($req) => response('ok')))
        ->toThrow(NotFoundHttpException::class);
});

it('sets the application locale and calls through when the locale is supported', function () {
    $request = requestWithLocale('en');
    $middleware = new SetLocale;

    $response = $middleware->handle($request, fn ($req) => response('ok'));

    expect($response->getContent())->toBe('ok');
    expect(app()->getLocale())->toBe('en');
});
