<?php

namespace Statamic\Addons\Meerkat;

use Statamic\API\Nav;
use Statamic\API\Config;
use Statamic\Extend\Listener;
use Statamic\API\User;
use Statamic\Extend\Extensible;
use Statamic\Addons\Meerkat\Comments\Manager;
use Statamic\Addons\Meerkat\Comments\Spam\Guard;
use Statamic\Addons\Meerkat\Comments\Spam\Detectors\GTUBEDetector;
use Statamic\Addons\Meerkat\Comments\Spam\Detectors\IPListDetector;
use Statamic\Addons\Meerkat\Comments\Spam\Detectors\AkismetDetector;
use Statamic\Addons\Meerkat\Permissions\AccessManager;

class MeerkatListener extends Listener
{
    use MeerkatHelpers, Extensible;

    protected $accessManager = null;

    private $hasResolvedAccessManager = false;

    /**
     * The events to be listened for, and the methods to call.
     *
     * @var array
     */
    public $events = [
        'cp.nav.created' => 'addNavItems',
        'cp.add_to_head' => 'addMeerkatCss',
        'Meerkat.registeringAvatarDrivers' => 'registerDefaultDrivers',
        'Meerkat.comment.creating' => 'commentCreated',
        'Meerkat.guard.starting' => 'loadSpamDetectors',
    ];

    public function __construct(AccessManager $accessManager)
    {
        parent::__construct();
        $this->addon_name = 'Meerkat';

        $this->accessManager = $accessManager;
    }

    public function loadSpamDetectors(Guard $guard)
    {
        $guard->registerDetector(new GTUBEDetector);
        $guard->registerDetector(new IPListDetector);

        $akismetKey = $this->getConfig('akismet_api_key', null);
        $akismetFrontPage = $this->getConfig('akismet_front_page', null);

        // We will determine if we should load the Akismet spam detector here.
        if ($akismetKey !== null && mb_strlen(trim($akismetKey)) > 0) {
            if ($akismetFrontPage == null || mb_strlen(trim($akismetFrontPage)) == 0) {
                $akismetFrontPage = Config::getSiteUrl();
            }

            $guard->registerDetector(new AkismetDetector($akismetKey, $akismetFrontPage));
        }

    }

    public function commentCreated($data)
    {
        // First, we must check to see if the site's administrators have enabled
        // the "Automatically Check New Comments for Spam" configuration item.
        if ($this->getConfigBool('auto_check_spam', true)) {

            // Now, if we are supposed to check new comments for spam, we have
            // to make sure that the comment is not an authenticated user
            // and that the site's administrator is not auto-publishing
            // comments from authenticated site users.
            if ($this->getConfigBool('auto_publish_authenticated_users', true) && auth()->user() !== null && auth()->user()->get('email') === $data['email']) {
                $data['spam'] = false;
                $data['spam_auto_delete'] = false;
                return $data;
            }

            /** @var Guard $guard */
            $guard = app(Guard::class);

            $data['spam'] = $guard->process($data);
            $data['spam_auto_delete'] = $this->getConfigBool('auto_delete_spam', false);

            return $data;
        }
    }

    public function registerDefaultDrivers($drivers)
    {
        $drivers['initials'] = '<div class="media-left meerkat-avatar-initials meerkat-avatar-initial-{{ item[\'initials\'].charAt(0).toLowerCase() }}"><span class="initials">{{ item[\'initials\'] }}</span></div>';
        $drivers['gravatar'] = '<div class="media-left"> <img alt="64x64" class="media-object" src="//www.gravatar.com/avatar/{{ item[\'gravatar\'] }}?s=80&d=mm"  style="width: 64px; height: 64px;"></div>';

        return $drivers;
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

    public function addNavItems($nav)
    {
        $this->resolveAccessManager();

        if ($this->accessManager->canViewComments() == false) {
            return;
        }

        $suffix = '';
        $pendingCount = 0;

        if (!$this->getConfigBool('auto_publish')) {
            /** @var Manager $manager */
            $manager = app(Manager::class);

            $pendingCount = $manager->countPending();

            if ($pendingCount > 0) {
                $suffix = " ({$pendingCount})";
            }
        }


        if (version_compare(STATAMIC_VERSION, '2.1.0') >= 0) {
            $comments = Nav::item($this->meerkatTrans('comments.comments'))->url('/' . CP_ROUTE . '/addons/meerkat?source=cp-nav')->icon('chat');

            $badgeMethodExists = method_exists($comments, 'badge');

            if ($badgeMethodExists && $pendingCount > 0) {
                $comments->badge($pendingCount);
            }
        } else {
            $comments = Nav::item($this->meerkatTrans('comments.comments') . $suffix)->url('/' . CP_ROUTE . '/addons/meerkat?source=cp-nav')->icon('chat');
        }

        $nav->addTo('content', $comments);
    }

    public function addMeerkatCss()
    {
        $this->resolveAccessManager();

        if ($this->accessManager->canViewComments() == false) {
            return;
        }

        if ($this->isMeerkatRequest()) {
            return '<link href="'.\cp_resource_url('../addons/Meerkat/css/meerkat.css').'?v=' . MeerkatAPI::version() . '" rel="stylesheet" />';
        }
    }

}
