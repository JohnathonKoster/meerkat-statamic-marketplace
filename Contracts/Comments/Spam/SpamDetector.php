<?php

namespace Statamic\Addons\Meerkat\Contracts\Comments\Spam;

interface SpamDetector
{

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