<?php

namespace Statamic\Addons\Meerkat\Comments\Spam\Detectors;

use Illuminate\Support\Str;
use Statamic\Addons\Meerkat\Contracts\Comments\Spam\SpamDetector;

class GTUBEDetector implements SpamDetector
{

    protected $success = true;

    protected $errorMessage = '';

    /**
     * Gets the name of the spam detector.
     *
     * @return string
     */
    public function getName()
    {
        return 'GTUBEDetector';
    }

    /**
     * Gets a value indicating if the detector succeeded.
     *
     * @return boolean
     */
    public function wasSuccess()
    {
        return $this->success;
    }

    /**
     * Gets an error message string, if available.
     *
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

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