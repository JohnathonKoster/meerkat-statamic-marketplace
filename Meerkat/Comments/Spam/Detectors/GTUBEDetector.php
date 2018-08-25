<?php

namespace Statamic\Addons\Meerkat\Comments\Spam\Detectors;

use Illuminate\Support\Str;
use Statamic\Addons\Meerkat\Contracts\Comments\Spam\SpamDetector;

class GTUBEDetector implements SpamDetector
{

    public function submitSpam($data)
    {
        return;
    }

    public function submitHam($data)
    {
        return;
    }

    /**
     * Checks if the comment should be flagged as spam or not.
     *
     * @param  array $data
     * @return bool
     */
    public function isSpam($data)
    {
        foreach ($data as $item) {
            if (is_string($item)) {
                if (Str::contains($item, 'XJS*C4JDBQADN1.NSBN3*2IDNEN*GTUBE-STANDARD-ANTI-UBE-TEST-EMAIL*C.34X')) {
                    return true;
                }
            }
        }

        return false;
    }

}