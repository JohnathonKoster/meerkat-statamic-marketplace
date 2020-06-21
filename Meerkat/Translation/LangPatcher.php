<?php

namespace Statamic\Addons\Meerkat\Translation;

use Statamic\Extend\Extensible;
use Statamic\API\Folder;

/**
 * Class LangPatcher
 *
 * Utility to find any language key that returns the key in the current locale. When
 * this happens, a translation string resolved to the fallback locale is used.
 *
 * @package Statamic\Addons\Meerkat\Translation
 */
class LangPatcher
{
    use Extensible;

    /**
     * The translator instance.
     *
     * @var \Statamic\Translation\Translator
     */
    private $translator;

    /**
     * The system's current locale.
     *
     * @var string
     */
    private $currentLocale = 'en';

    /**
     * The fallback locale to use.
     *
     * @var string
     */
    private $fallbackLocale = 'en';

    public function __construct()
    {
        $this->addon_name = 'Meerkat';
        $this->translator = app('translator');
        $this->currentLocale = $this->translator->locale();
    }

    /**
     * Locates any translation strings not present in the configured locale.
     *
     * @return array
     */
    public function getPatches()
    {
        if ($this->currentLocale == $this->fallbackLocale) {
            return [];
        }

        // Construct a path to our potential language directory.
        $localeDirectory = $this->getDirectory() . '/resources/lang/' . $this->currentLocale;
        $fallbackDirectory = $this->getDirectory() . '/resources/lang/' . $this->fallbackLocale;

        // If the configured locale exists, lets find which translation strings do not exist.
        if (Folder::exists($localeDirectory) && Folder::exists($fallbackDirectory)) {
            $fallbackLocale = collect(Folder::getFiles($fallbackDirectory))->localize();
            $targetLocale = collect(Folder::getFiles($localeDirectory))->localize();

            $targetFlat = $targetLocale->all();
            $patches = [];

            foreach ($fallbackLocale as $localeCategory => $categoryStrings) {
                if (is_array($categoryStrings)) {
                    foreach ($categoryStrings as $translationKey => $translationValue) {
                        if (array_key_exists($localeCategory, $targetFlat)) {

                            if (array_key_exists($translationKey, $targetFlat[$localeCategory]) == false) {
                                if (array_key_exists('Meerkat::' . $localeCategory, $patches) == false) {
                                    $patches['Meerkat::'.$localeCategory] = [];
                                }

                                $patches['Meerkat::'.$localeCategory][$translationKey] = $translationValue;
                            }
                        } else {
                            if (array_key_exists('Meerkat::' . $localeCategory, $patches) == false) {
                                $patches['Meerkat::'.$localeCategory] = [];
                            }

                            $patches['Meerkat::'.$localeCategory][$translationKey] = $translationValue;
                        }
                    }
                }
            }

            return $patches;
        }

        return [];
    }

}