<?php

namespace Statamic\Addons\Meerkat;

use Statamic\Extend\Extensible;
use Statamic\Extend\ServiceProvider;
use Illuminate\Contracts\Http\Kernel;
use Statamic\Addons\Meerkat\SettingsPatcher;
use Statamic\Addons\Meerkat\Comments\Manager;
use Statamic\Addons\Meerkat\Comments\Spam\Guard;
use Statamic\Addons\Meerkat\Extend\AvatarLoader;
use Statamic\Addons\Meerkat\Platform\PlatformChecks;
use Statamic\Addons\Meerkat\Http\Middleware\CompassMiddleware;
use Statamic\Addons\Meerkat\Http\Composers\JavaScriptComposer;

class MeerkatServiceProvider extends ServiceProvider
{
    use Extensible;

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    public $defer = false;

    protected $platformChecks = null;

    public function boot()
    {
        $this->addon_name = 'Meerkat';
        require_once __DIR__.'/bootstrap/helpers.php';

        $this->registerDependencies();

        // We need to register the view composers in the provider's
        // boot() method so that we can get our view composers
        // added after Statamic's view composer has been
        // added. The boot() method is called after
        // all providers have been registered.
        $this->registerViewComposers();
        $this->loadAvatarDrivers();
        $this->registerSpamGuard();
    }

    protected function registerDependencies()
    {
        if ($this->platformChecks == null) {
            $this->platformChecks = new PlatformChecks;
        }

        $this->platformChecks->checkDependencies();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->addon_name = 'Meerkat';
        SettingsPatcher::ensurePathsExist();
        $this->app->singleton(Manager::class, Manager::class);
        $this->app->singleton(AvatarLoader::class, AvatarLoader::class);
        $this->app->singleton(Guard::class, Guard::class);

        $submitResults = $this->getConfigBool('guard_submit_results', false);
        $this->app->make(Guard::class)->doSubmitResults($submitResults);
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

    public function __call($method, $parameters)
    {
        parent::__call($method, $parameters);
    }

}
