<?php

namespace Statamic\Addons\Meerkat\Extend\DefaultFilters;

use Statamic\Addons\Meerkat\Comments\Filters\CommentFilter;
use Statamic\Addons\Meerkat\Extend\ThemeFilters;
use Statamic\Addons\Meerkat\Helpers\ListInput;

/**
 * Class IsFilters
 *
 * Contains the is:<property> related Meerkat tags.
 *
 * @package Statamic\Addons\Meerkat\Extend\DefaultFilters
 * @since 1.5.85
 */
class IsFilters
{

    /**
     * Registers the default is:<> filters.
     *
     * @param ThemeFilters $filters The filter manager.
     */
    public function register(ThemeFilters $filters)
    {
        $filters->filterWithTagContext('is:spam', function ($comments) {
            $includeSpam = ListInput::parseBoolean($this->get('comparison', false));

            return $comments->filter(function ($comment) use ($includeSpam) {
                $isSpam = false;

                if (array_key_exists('spam', $comment) && $comment['spam'] == true) {
                    $isSpam = true;
                }

                if ($includeSpam) {
                    if ($isSpam) {
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    if ($isSpam) {
                        return false;
                    }
                }

                return true;
            });
        }, 'comparison');

        $filters->filterWithTagContext('is:published', function ($comments) {
            $includePublished = ListInput::parseBoolean($this->get('comparison', true));

            return $comments->filter(function ($comment) use ($includePublished) {
                $isPublished = true;

                if (array_key_exists('published', $comment) && $comment['published'] == false) {
                    $isPublished = true;
                }

                if ($includePublished) {
                    if ($isPublished) {
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    if ($isPublished) {
                        return false;
                    }
                }

                return true;
            });
        }, 'comparison');
    }

}