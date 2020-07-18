<?php

namespace Statamic\Addons\Meerkat\Extend\DefaultFilters;

use Statamic\Addons\Meerkat\Extend\ThemeFilters;
use Statamic\Addons\Meerkat\Helpers\ListInput;

/**
 * Class UserFromAuth
 *
 * Contains the user:<> default Meerkat comments.
 *
 * @package Statamic\Addons\Meerkat\Extend\DefaultFilters
 * @since 1.5.85
 */
class UserFromAuth
{

    /**
     * Registers the default Meerkat user:<> filters.
     *
     * @param ThemeFilters $filters The filter manager.
     */
    public function register(ThemeFilters $filters)
    {
        $filters->filter('user:from_auth', function ($comments) {
            $includeUsers = ListInput::parseBoolean($this->get('comparison', false));

            return $comments->reject(function ($comment) use ($includeUsers) {
                $commentHasUser = array_key_exists('authenticated_user', $comment);

                if ($includeUsers) {
                    if ($commentHasUser) {
                        return false;
                    }
                } else {
                    if (!$commentHasUser) {
                        return false;
                    }
                }

                return true;
            });
        }, 'comparison');
    }

}