<?php

namespace Statamic\Addons\Meerkat\Extend\DefaultFilters;

use Statamic\Addons\Meerkat\Extend\ThemeFilters;
use Statamic\Addons\Meerkat\Helpers\ListInput;

/**
 * Class UserIn
 *
 * Contains the default user:<> Meerkat filters.
 *
 * @package Statamic\Addons\Meerkat\Extend\DefaultFilters
 * @since 1.5.85
 */
class UserIn
{

    /**
     * Registers the default user:<> Meerkat filters.
     *
     * @param ThemeFilters $filters The filter manager.
     */
    public function register(ThemeFilters $filters)
    {
        $filters->filter('user:in', function ($comments) {
            $tempUsers = ListInput::parse($this->get('users', []));
            $userList = [];

            foreach ($tempUsers as $value) {
                if ($value === '*current*' && $this->hasUser()) {
                    $user = $this->getUser();

                    $userList[] = $user->id();
                } else {
                    $userList[] = $value;
                }
            }

            return $comments->filter(function ($comment) use ($userList) {
                if (array_key_exists('authenticated_user', $comment) == false) {
                    return false;
                }

                return in_array($comment['authenticated_user'], $userList);
            });
        }, 'users');

        $filters->filter('user:not_in', function ($comments) {
            $tempUsers = ListInput::parse($this->get('users', []));
            $userList = [];

            foreach ($tempUsers as $value) {
                if ($value === '*current*' && $this->hasUser()) {
                    $user = $this->getUser();

                    $userList[] = $user->id();
                } else {
                    $userList[] = $value;
                }
            }

            return $comments->filter(function ($comment) use ($userList) {
                if (array_key_exists('authenticated_user', $comment) == false) {
                    return false;
                }

                return in_array($comment['authenticated_user'], $userList) == false;
            });
        }, 'users');
    }

}