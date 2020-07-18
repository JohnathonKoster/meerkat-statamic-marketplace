<?php

namespace Statamic\Addons\Meerkat\Support\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Addons\Meerkat\Extend\ThemeFilters;

class Comments extends Facade
{

    protected static function getFacadeAccessor()
    {
        return ThemeFilters::class;
    }

}