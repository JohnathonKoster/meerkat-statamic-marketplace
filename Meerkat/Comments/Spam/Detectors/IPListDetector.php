<?php

namespace Statamic\Addons\Meerkat\Comments\Spam\Detectors;

use Statamic\Extend\Extensible;
use Statamic\Addons\Meerkat\Contracts\Comments\Spam\SpamDetector;

class IPListDetector implements SpamDetector
{
    use Extensible;

    /**
     * The IP addresses that should always be allowed.
     *
     * @var array
     */
    protected $whiteList = [];

    /**
     * The IP address that should always be blocked.
     *
     * @var array
     */
    protected $blackList = [];

    public function __construct()
    {
        $this->addon_name = 'Meerkat';

        $list = $this->getConfig('iplist', []);

        if (array_key_exists('allow', $list)) {
            $this->whiteList = $list['allow'];
        }

        if (is_string($this->whiteList)) {
            $this->whiteList = [];
        }

        if (array_key_exists('block', $list)) {
            $this->blackList = $list['block'];
        }

        if (is_string($this->blackList)) {
            $this->blackList = [];
        }
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
        if (array_key_exists('user_ip', $data)) {
            $ipAddress = array_get($data['user_ip'], null);
        } else {
            return false;
        }

        if ($ipAddress !== null) {
            if (in_array($ipAddress, $this->whiteList)) {
                return false;
            } else {
                return in_array($ipAddress, $this->blackList);
            }
        }

        return false;
    }

}