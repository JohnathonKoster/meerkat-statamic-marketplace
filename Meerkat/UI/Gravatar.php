<?php

namespace Statamic\Addons\Meerkat\UI;

trait Gravatar
{

    /**
     * Maps to {{ meerkat:gravatar-value }}
     *
     * @return string
     */
    public function gravatarValue()
    {
        $email = $this->get('email');
        return md5($email);
    }

    /**
     * Maps to {{ meerkat:gravatar }}
     *
     * @return string
     */
    public function gravatar()
    {
        $email = $this->get('email');

        return '//www.gravatar.com/avatar/'.md5($email).'?';
    }

}