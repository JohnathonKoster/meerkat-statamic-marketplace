<?php

namespace Statamic\Addons\Meerkat\Extend;

use Statamic\Extend\Extensible;

class AvatarLoader
{
    use Extensible;

    public function __construct()
    {
        $this->addon_name = 'Meerkat';
    }

    /**
     * The Avatar driver names and their templates.
     *
     * @var array
     */
    protected $avatars = [];

    /**
     * Loads the Avatar drivers that can be used on the client side.
     */
    public function loadAvatars()
    {
        $drivers = [];
        $avatars = $this->emitEvent('registeringAvatarDrivers', [$drivers]);

        foreach ($avatars as $avatarDrivers) {
            if (count($avatarDrivers) > 0) {
                $this->avatars = array_merge($this->avatars, $avatarDrivers);
            }
        }

    }

    /**
     * Gets the avatar drivers and associated templates.
     *
     * @return array
     */
    public function getAvatars()
    {
        return $this->avatars;
    }

}