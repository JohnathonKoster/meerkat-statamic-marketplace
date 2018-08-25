<?php

namespace Statamic\Addons\Meerkat;

class SettingsPatcher
{

    const PATH_TARGET_SETTINGS = 'settings/addons/meerkat.yaml';
    const PATH_SOURCE_SETTINGS = 'addons/Meerkat/default.yaml';
    const PATH_SOURCE_FORMSET = 'addons/Meerkat/formset.yaml';

    // Needs to match the generated URL for case sensitive file systems.
    const PATH_TARGET_FORMSET = 'settings/formsets/meerkat.yaml';

    public static function ensurePathsExist()
    {
      if (! file_exists(site_path(self::PATH_TARGET_SETTINGS))) {
        file_put_contents(site_path(self::PATH_TARGET_SETTINGS), file_get_contents(site_path(self::PATH_SOURCE_SETTINGS)));
      }

      if (! file_exists(site_path(self::PATH_TARGET_FORMSET))) {
          file_put_contents(site_path(self::PATH_TARGET_FORMSET), file_get_contents(site_path(self::PATH_SOURCE_FORMSET)));
      }
    }

    public static function loadMeerkatHelpers()
    {
        require_once __DIR__.'/bootstrap/helpers.php';
    }

}