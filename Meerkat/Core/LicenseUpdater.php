<?php

namespace Statamic\Addons\Meerkat\Core;

use Statamic\API\YAML;

class LicenseUpdater
{

    public function updateLicense($licenseKey)
    {
        try {
            $settingPath = settings_path('/addons/meerkat.yaml');
            $settingsContent = file_get_contents($settingPath);
            $settings = YAML::parse($settingsContent);
            $settings['license_key'] = $licenseKey;
            $settings = YAML::dump($settings);
            file_put_contents($settingPath, $settings);

            return true;
        } catch (\Exception $e) { }

        return false;
    }

}