<?php

namespace Statamic\Addons\Meerkat\Http\Composers;

use Statamic\API\User;
use Statamic\Extend\Extensible;
use Illuminate\Contracts\View\View;
use Statamic\Addons\Meerkat\MeerkatHelpers;
use Statamic\Addons\Meerkat\API\URL;
use Statamic\Addons\Meerkat\MeerkatAPI;
use Statamic\Addons\Meerkat\Extend\AvatarLoader;
use Statamic\Addons\Meerkat\Translation\LangPatcher;
use Statamic\Addons\Meerkat\Permissions\AccessManager;

class JavaScriptComposer
{
    use Extensible, MeerkatHelpers;
    

    const LIST_START = '{{-- start:list --}}';
    const LIST_END   = '{{-- end:list --}}';

    protected $accessManager = null;

    private $hasResolvedAccessManager = false;

    public function __construct(AccessManager $accessManager)
    {
        $this->addon_name = 'Meerkat';
        $this->accessManager = $accessManager;
    }

    /**
     * Gets the JSON representation of the Meerkat configuration.
     *
     * @return string
     */
    private function getSettings()
    {
        return json_encode([
           'avatar_driver' => $this->getConfig('cp_avatar_driver')
        ]);
    }

    private function resolveAccessManager()
    {
        if ($this->hasResolvedAccessManager == false) {
            $this->accessManager->setUser(User::getCurrent());
            $this->accessManager->setPermissions($this->getConfig('permissions'));
            $this->accessManager->resolve();

            $this->hasResolvedAccessManager = true;
        }
    }

    private function getStreamTemplate() {
        $meerkatPath = addons_path('/Meerkat/resources/views/');
        view()->addNamespace('Meerkat', $meerkatPath);
        $content = '<div id="meerkat-publisher-stream">'.view('Meerkat::streams/stream')->with('filter', 'all')->with('hideManagement', true)->__toString().'</div>';
        return json_encode($content);
    }

    public function compose(View $view)
    {
        $this->resolveAccessManager();

        if ($this->accessManager->canViewComments() == false) {
            return;
        }

        if (!$this->isMeerkatRequest()) {
            return;
        }

        $scripts = '';

        if (isset($view['scripts'])) {
            $scripts = $view['scripts'];
        }

        $meerkatPermissionsSet = $this->accessManager->toPermissionSet();
        $permissions = base64_encode(\json_encode($meerkatPermissionsSet->toArray()));

        $scripts .= '<script>if (typeof Meerkat == "undefined") { Meerkat = {}; } ; Meerkat.countsUrl = "' . $this->meerkatCpPath() . 'addons/meerkat/counts"; Meerkat.permissions = Object.freeze(JSON.parse(atob("'.$permissions.'")));</script>';
        $scripts .= '<script src="' . URL::prependSiteRoot(URL::assemble(RESOURCES_ROUTE, 'addons', 'Meerkat', 'js/control-panel.js?v=' . MeerkatAPI::version())) . '"></script>';


        $scripts .= '<script src="' . URL::prependSiteRoot(URL::assemble(RESOURCES_ROUTE, 'addons', 'Meerkat', 'js/meerkat.js?v=' . MeerkatAPI::version())) . '"></script>' . $scripts;
        $scripts .= '<script>Meerkat.version = "' . MeerkatAPI::version() . '";</script>';

        // Add the Meerkat configuration to the mix.
        $scripts .= '<script>Meerkat.config = '.$this->getSettings().';</script>';

        if ($this->isCpDashboard()) {
            $dashboardPatches = file_get_contents(realpath(__DIR__.'/../../resources/meerkatCommentStats.js'));

            $scripts .= '<script type="text/javascript">'.$dashboardPatches.'</script><script src="' . URL::prependSiteRoot(URL::assemble(RESOURCES_ROUTE, 'addons', 'Meerkat', 'js/dashboard.js?v=' . MeerkatAPI::version())) . '"></script>' . $scripts;
        }

        $langPatcher = new LangPatcher();

        $langPatches = $langPatcher->getPatches();

        // Control Panel translation structure is:
        //     window.Statamic.translations.addons.Meerkat::<NAMESPACE>{obj:key>translation}

        if ($langPatches != null && is_array($langPatches) && count($langPatches) > 0) {
            $scripts .= '<script>';
            foreach ($langPatches as $patchCategory => $translationPatches) {
                $jsCategory = 'addons.'.$patchCategory;

                $scripts .= 'if (typeof Statamic.translations[\''.$jsCategory.'\'] === \'undefined\') { Statamic.translations[\''.$jsCategory.'\'] = {}; }';

                if ($translationPatches != null && is_array($translationPatches) && count($translationPatches) > 0) {
                    foreach ($translationPatches as $localeKey => $localeValue) {
                        $jsValue = str_replace('\'', '\\\'', $localeValue);
                        $scripts .= 'Statamic.translations[\''.$jsCategory.'\'][\''.$localeKey.'\'] = \''.$jsValue.'\';';
                    }
                }
            }
            $scripts .= '</script>';
        }


        /** @var AvatarLoader $avatarLoader */
        $avatarLoader = app(AvatarLoader::class);
        $avatarPartial = $avatarLoader->getAvatars()[$this->getConfig('cp_avatar_driver')];
        $avatarPartial = strip_tags($avatarPartial, '<div><img><span>');
        $scripts .= '<script>Meerkat.setAvatarTemplate("'.addslashes($avatarPartial).'");</script>';

        if ($this->isMeerkatPublisherRequest()) {
            $scripts .= '<script>Meerkat.Publisher.publisherStream = '.$this->getStreamTemplate().';</script>';
        }

        $view->with('scripts', $scripts);
    }

}