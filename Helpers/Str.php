<?php

namespace Statamic\Addons\Meerkat\Helpers;

class Str
{

    /**
     * Creates initials from the given name string.
     *
     * @param  string $name
     * @param  bool   $onlyCapitals
     * @param  string $separator
     * @return string
     */
    public static function initials($name, $onlyCapitals = false, $separator = ' ')
    {
        $initials = '';

        $token = strtok($name, $separator);
        while ($token !== false) {
            $character = mb_substr($token, 0, 1);

            if ($onlyCapitals && mb_strtoupper($character) !== $character) {
                $token = strtok($separator);
                continue;
            }

            $initials .= $character;
            $token = strtok($separator);
        }
        return $initials;
    }

}