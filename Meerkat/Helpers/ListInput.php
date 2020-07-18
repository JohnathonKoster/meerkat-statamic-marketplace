<?php

namespace Statamic\Addons\Meerkat\Helpers;

use Statamic\API\Helper;

/**
 * Class ListInput
 *
 * Providers utilities for managing input from strings.
 *
 * @package Statamic\Addons\Meerkat\Helpers
 * @since 1.5.85
 */
class ListInput
{

    /**
     * Parses the string input and returns a boolean value.
     *
     * @param string $input The string input.
     * @return bool
     */
    public static function parseBoolean($input)
    {
        if ($input === 'true') {
            return true;
        }

        return false;
    }

    /**
     * Parses the input into an array. Comma-delimited values are allowed.
     *
     * @param string $input The input to parse.
     * @return array
     */
    public static function parse($input)
    {
        if (is_string($input)) {
            $temp = explode(',', $input);

            if ($temp == false) {
                $userList = [];
            } else {
                $temp = array_map('trim', $temp);
            }
            $input = $temp;
        }

        return Helper::ensureArray($input);
    }

}