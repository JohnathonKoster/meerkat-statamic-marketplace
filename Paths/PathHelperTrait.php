<?php

namespace Statamic\Addons\Meerkat\Paths;

trait PathHelperTrait
{

    protected function getMeerkatPath($path)
    {
        return config('filesystems.disks.content.root').'/comments/'.$path;
    }

}