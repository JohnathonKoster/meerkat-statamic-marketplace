<?php

namespace Statamic\Addons\Meerkat\Core\Compass;

use Log;
use GuzzleHttp\Client;
use Statamic\API\Cache;
use Statamic\API\Config;
use Illuminate\Http\Request;
use Statamic\Extend\Extensible;
use Statamic\Addons\Meerkat\MeerkatAPI;
use GuzzleHttp\Exception\RequestException;

class Compass
{
    use Extensible;

    const ENDPOINT = 'https://compass.stillat.com/v1/m';

    const RESPONSE_CACHE_KEY = 'meerkat_compass_response';

    private $response;

    public function __construct()
    {
        $this->addon_name = 'Meerkat';
    }

    public function check()
    {
        if ($this->hasCachedResponse()) {
            return $this->response = $this->getCachedResponse();
        }

        $this->getBearings();
        
        return $this->response;
    }

    private function getBearings()
    {
        $response = $this->getDefaultResponse();

        try {
            $client = new Client;
            $response = $client->request('POST', self::ENDPOINT, ['json' => $this->getPayload()]);
            $response = json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            Log::notice('The Stillat Compass server could not be reached.');
        } catch (Exception $e) {
            Log::error('An error occurred when contacting the Stillat Compass server.');
        }

        $this->response = $response;
        $this->cacheResponse();
    }

    public function isLicenseValid()
    {
        return array_get($this->response, 'license_valid');
    }

    public function isOnPublicDomain()
    {
        return array_get($this->response, 'public_domain');
    }

    public function isOnCorrectDomain()
    {
        return array_get($this->response, 'correct_domain');
    }

    private function cacheResponse()
    {
        Cache::put(self::RESPONSE_CACHE_KEY, $this->response, 60);
    }

    private function hasCachedResponse()
    {
        if (! Cache::has(self::RESPONSE_CACHE_KEY)) {
            return false;
        }
        if ($this->getConfig('license_key') !== array_get($this->getCachedResponse(), 'license_key')) {
            return false;
        }

        return true;
    }

    private function getCachedResponse()
    {
        return Cache::get(self::RESPONSE_CACHE_KEY);
    }

    private function getDefaultResponse()
    {
        return [
            'license_key'    => $this->getConfig('license_key'),
            'latest_version' => MeerkatAPI::version(),
            'license_valid'  => false
        ];
    }

    private function getPayload()
    {
        return [
            'license_key'      => $this->getConfig('license_key'),
            'version'          => MeerkatAPI::version(),
            'statamic_version' => STATAMIC_VERSION,
            'php_version'      => PHP_VERSION,
            'request'          => [
                'domain'       => request()->server('HTTP_HOST'),
                'ip'           => request()->ip(),
                'port'         => request()->getPort()
            ]
        ];
    }

}