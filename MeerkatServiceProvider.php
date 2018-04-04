<?php

namespace Statamic\Addons\Meerkat;

use Statamic\Extend\ServiceProvider;
use Illuminate\Contracts\Http\Kernel;
use Statamic\Addons\Meerkat\Comments\Manager;
use Statamic\Addons\Meerkat\Comments\Spam\Guard;
use Statamic\Addons\Meerkat\Extend\AvatarLoader;
use Statamic\Addons\Meerkat\Http\Middleware\CompassMiddleware;
use Statamic\Addons\Meerkat\Http\Composers\JavaScriptComposer;

class MeerkatServiceProvider extends ServiceProvider
{

    const PATH_TARGET_SETTINGS = 'settings/addons/meerkat.yaml';
    const PATH_SOURCE_SETTINGS = 'addons/Meerkat/default.yaml';
    const PATH_SOURCE_FORMSET = 'addons/Meerkat/formset.yaml';

    // Needs to match the generated URL for case sensitive file systems.
    const PATH_TARGET_FORMSET = 'settings/formsets/Meerkat.yaml';


    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    public $defer = true;

    public function boot()
    {
        require_once __DIR__.'/bootstrap/helpers.php';

        // We need to register the view composers in the provider's
        // boot() method so that we can get our view composers
        // added after Statamic's view composer has been
        // added. The boot() method is called after
        // all providers have been registered.
        $this->registerViewComposers();
        $this->loadAvatarDrivers();
        $this->registerSpamGuard();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->ensurePathsExist();
        $this->app->singleton(Manager::class, Manager::class);
        $this->app->singleton(AvatarLoader::class, AvatarLoader::class);
        $this->app->singleton(Guard::class, Guard::class);

        $submitResults = $this->getConfigBool('guard_submit_results', false);
        $this->app->make(Guard::class)->doSubmitResults($submitResults);
    }

    protected function ensurePathsExist()
    {
        if (! file_exists(site_path(self::PATH_TARGET_SETTINGS))) {
            file_put_contents(site_path(self::PATH_TARGET_SETTINGS), file_get_contents(site_path(self::PATH_SOURCE_SETTINGS)));
        }

        if (! file_exists(site_path(self::PATH_TARGET_FORMSET))) {
            file_put_contents(site_path(self::PATH_TARGET_FORMSET), file_get_contents(site_path(self::PATH_SOURCE_FORMSET)));
        }

    }

    /**
     * Registers the spam guard.
     */
    protected function registerSpamGuard()
    {
        $this->emitEvent('guard.starting', app(Guard::class));
    }

    /**
     * Registers Meerkat's view composers.
     */
    protected function registerViewComposers()
    {
        view()->composer('partials.scripts', JavaScriptComposer::class);
    }

    /**
     * Loads the avatar driver and templates.
     */
    protected function loadAvatarDrivers()
    {
        app(AvatarLoader::class)->loadAvatars();
    }

}
