<?php

namespace Statamic\Addons\Meerkat\Contracts\Comments\Spam;

interface SpamDetector
{

    /**
     * Gets the name of the spam detector.
     *
     * @return string
     */
    public function getName();

    /**
     * Gets a value indicating if the detector succeeded.
     *
     * @return boolean
     */
    public function wasSuccess();

    /**
     * Gets an error message string, if available.
     *
     * @return string
     */
    public function getErrorMessage();

    /**
     * Indicates if the detector has determined the data is spam.
     *
     * @param  array $data
     * @return bool
     */
    public function isSpam($data);

    /**
     * Submits spam to the spam service.
     *
     * @param  array $data
     * @return mixed
     */
    public function submitSpam($data);

    /**
     * Submits ham to the spam service.
     *
     * @param $data
     * @return mixed
     */
    public function submitHam($data);

}