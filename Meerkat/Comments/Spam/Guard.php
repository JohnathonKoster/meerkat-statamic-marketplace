<?php

namespace Statamic\Addons\Meerkat\Comments\Spam;

use Statamic\Addons\Meerkat\Contracts\Comments\Spam\SpamDetector;

class Guard
{

    /**
     * An array of SpamDetector instances.
     *
     * @var array
     */
    protected $spamDetectors = [];

    /**
     * Indicates if spam was detected.
     *
     * @var bool
     */
    protected $spamDetected = false;

    /**
     * The spam processing results.
     *
     * @var array
     */
    protected $results = [];

    protected $autoSubmitResults = false;

    /**
     * Processes the data to determine if it is spam.
     *
     * @param  array $data
     * @return bool
     */
    public function process($data)
    {
        // Reset the spam detected value.
        $this->spamDetected = false;

        // Skip over comments that have been explicitly marked as not spam
        // either by the end user or by some internal process.
        if (isset($data['spam']) && boolval($data['spam']) === false) {
            return $this->spamDetected;
        }

        /** @var SpamDetector $detector */
        foreach ($this->spamDetectors as $detector) {

            $spam = $detector->isSpam($data);

            $this->results[spl_object_hash($detector)] = [
                get_class($detector),
                $spam
            ];

            if ($spam) {
                $this->spamDetected = true;

                if ($this->autoSubmitResults) {
                    $this->submitSpam($data);
                }
            }
        }

        return $this->spamDetected;
    }

    /**
     * Determines if spam was detected.
     *
     * @return bool
     */
    public function spamDetected()
    {
        return $this->spamDetected;
    }

    public function doSubmitResults($submitResults)
    {
        $this->autoSubmitResults = $submitResults;
    }

    /**
     * Adds a new SpamDetector instance to the list.
     *
     * @param SpamDetector $detector
     */
    public function registerDetector(SpamDetector $detector)
    {
        $this->spamDetectors[] = $detector;
    }

    public function submitSpam($data)
    {
        /** @var SpamDetector $detector */
        foreach ($this->spamDetectors as $detector) {
            $detector->submitSpam($data);
        }
    }

    public function submitHam($data)
    {
        /** @var SpamDetector $detector */
        foreach ($this->spamDetectors as $detector) {
            $detector->submitHam($data);
        }
    }

}