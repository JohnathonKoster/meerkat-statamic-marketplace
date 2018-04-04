<?php

namespace Statamic\Addons\Meerkat;

use Statamic\Extend\Widget;
use Statamic\Addons\Meerkat\Comments\Manager;
use Statamic\Addons\Meerkat\Comments\Metrics\CommentMetrics;

class MeerkatWidget extends Widget
{

    private function getStats()
    {
        $manager = app(Manager::class);
        return with(new CommentMetrics)->setComments($manager->all())->toArray();
    }

    /**
     * The HTML that should be shown in the widget
     *
     * @return string
     */
    public function html()
    {
        $stats = $this->getStats();
        return $this->view('widget_overview')->with('stats', $stats);
    }
}
