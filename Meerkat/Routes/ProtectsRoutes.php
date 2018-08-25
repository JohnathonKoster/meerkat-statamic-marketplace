<?php

namespace Statamic\Addons\Meerkat\Routes;

use Illuminate\Support\Str;
use Illuminate\Routing\RouteCollection;

trait ProtectsRoutes
{
    
    protected function protectRoutes()
    {
        /** @var RouteCollection $routes */
        $routes = app('routes');
        $currentRoute = $routes->match(request());
        
        if ($currentRoute->matches(request(), true)) {
            $callback = Str::parseCallback($currentRoute->getActionName(), null);

            if ($callback !== null && $callback[0] == self::class && in_array($callback[1], $this->protectedRoutes)) {
                $this->middleware('auth');
            }
        }
    }



}