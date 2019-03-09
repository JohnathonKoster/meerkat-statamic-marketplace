<?php

namespace Statamic\Addons\Meerkat\Http\Composers;

use Illuminate\Support\Str;
use Statamic\Extend\Extensible;
use Illuminate\Contracts\View\View;
use Statamic\Addons\Meerkat\API\URL;
use Statamic\Addons\Meerkat\MeerkatAPI;
use Statamic\Addons\Meerkat\Extend\AvatarLoader;

class JavaScriptComposer
{
    use Extensible;

    const LIST_START = '{{-- start:list --}}';
    const LIST_END   = '{{-- end:list --}}';

    public function __construct()
    {
        $this->addon_name = 'Meerkat';
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

    private function getStreamTemplate() {
        $meerkatPath = addons_path('/Meerkat/resources/views/');
        view()->addNamespace('Meerkat', $meerkatPath);
        $content = '<div id="meerkat-publisher-stream">'.view('Meerkat::streams/stream')->with('filter', 'all')->with('hideManagement', true)->__toString().'</div>';
        return json_encode($content);
    }

    public function compose(View $view)
    {
        $scripts = '';

        if (isset($view['scripts'])) {
            $scripts = $view['scripts'];
        }

        $scripts .= '<script>if (typeof Meerkat == "undefined") { Meerkat = {}; } ; Meerkat.countsUrl = "' . meerkat_cppath() . 'addons/meerkat/counts";</script>';
        $scripts = $scripts.'<script src="' . URL::prependSiteRoot(URL::assemble(RESOURCES_ROUTE, 'addons', 'Meerkat', 'js/control-panel.js?v=' . MeerkatAPI::version())) . '"></script>';

        if (is_cp_dashboard()) {
            $dashboardPatches = file_get_contents(realpath(__DIR__.'/../../resources/meerkatCommentStats.js'));

            $scripts .= '<script type="text/javascript">'.$dashboardPatches.'</script><script src="' . URL::prependSiteRoot(URL::assemble(RESOURCES_ROUTE, 'addons', 'Meerkat', 'js/dashboard.js?v=' . MeerkatAPI::version())) . '"></script>' . $scripts;
            
            $view->with('scripts', $scripts);
            return;
        }

        if (!is_meerkat_request()) {
            return;
        }

        $scripts = '<script src="' . URL::prependSiteRoot(URL::assemble(RESOURCES_ROUTE, 'addons', 'Meerkat', 'js/meerkat.js?v=' . MeerkatAPI::version())) . '"></script>' . $scripts;
        $scripts .= '<script>Meerkat.version = "' . MeerkatAPI::version() . '";</script>';

        // Add the Meerkat configuration to the mix.
        $scripts .= '<script>Meerkat.config = '.$this->getSettings().';</script>';

        /** @var AvatarLoader $avatarLoader */
        $avatarLoader = app(AvatarLoader::class);
        $avatarPartial = $avatarLoader->getAvatars()[$this->getConfig('cp_avatar_driver')];
        $avatarPartial = strip_tags($avatarPartial, '<div><img><span>');
        $scripts .= '<script>Meerkat.setAvatarTemplate("'.addslashes($avatarPartial).'");</script>';

        if (is_meerkat_publisher_request()) {
            $scripts .= '<script>Meerkat.Publisher.publisherStream = '.$this->getStreamTemplate().';</script>';
        }


        $scripts .= '';

        $view->with('scripts', $scripts);

    }

}