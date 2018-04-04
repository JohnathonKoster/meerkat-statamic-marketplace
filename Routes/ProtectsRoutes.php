<?php

namespace Statamic\Addons\Meerkat\Routes;

use Illuminate\Support\Str;
use Illuminate\Routing\RouteCollection;
use Statamic\Addons\Meerkat\Core\Compass\Compass;

trait ProtectsRoutes
{

    protected $compass;

    protected function getCompass()
    {
        if ($this->compass == null) {
            $this->compass = app(Compass::class);
        }

        $this->compass->check();

        return $this->compass;
    }

    protected function isLicensed()
    {
        $compass = $this->getCompass();

        if (!$compass->isOnPublicDomain()) {
            return true;
        }

        if ($compass->isLicenseValid() && $compass->isOnCorrectDomain()) {
            return true;
        }

        if ($compass->isLicenseValid() && ! $compass->isOnCorrectDomain()) {
            return null;
        }

        return false;
    }

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