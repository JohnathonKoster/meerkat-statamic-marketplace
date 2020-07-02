<?php

namespace Statamic\Addons\Meerkat\Routes;

use Illuminate\Support\Str;
use Illuminate\Routing\RouteCollection;
use Illuminate\Contracts\Auth\Guard;
use \Illuminate\Auth\Access\UnauthorizedException;

trait ProtectsRoutes
{

    protected function protectRoutes()
    {
        /** @var RouteCollection $routes */
        $request = request();
        $routes = app('routes');
        $currentRoute = $routes->match($request);
        $doGuard = false;

        if ($currentRoute->matches($request, true)) {
            $callback = Str::parseCallback($currentRoute->getActionName(), null);

            if ($callback !== null && $callback[0] == self::class && in_array($callback[1], $this->protectedRoutes)) {
                $this->middleware('auth');
                $doGuard = true;
            }

            $controllerParam = $currentRoute->getParameter('controller');
            $routedMethod = $currentRoute->getParameter('method');


            if ($controllerParam == "Meerkat") {
                if (in_array($routedMethod, $this->secondaryProtected))
                {
                    $this->middleware('auth');
                    $doGuard = true;
                }
            }
        }

        if ($doGuard)
        {
            $auth = app(Guard::class);

            if ($auth->guest()) {
                if ($request->ajax()) {
                    return response('Unauthorized.', 401);
                } else {
                    abort(403);
                }
            }
        }
    }

}