<?php

namespace Statamic\Addons\Meerkat\Helpers;

use Statamic\API\URL;
use Statamic\API\Request;
use Statamic\Presenters\PaginationPresenter;
use Illuminate\Pagination\LengthAwarePaginator;

trait CollectionTagHelpers
{

    /**
     * @var bool
     */
    private $paginated;

    /**
     * @var int
     */
    private $limit;

    /**
     * @var int
     */
    private $offset;

    /**
     * @var array
     */
    private $pagination_data;

    /**
     * @var int|null
     */
    private $total_results;

    protected function limit()
    {
        $limit = $this->getInt('limit');
        $this->limit = ($limit == 0) ? $this->collection->count() : $limit;
        $this->offset = $this->getInt('offset');

        if ($this->getBool('paginate')) {
            $this->paginate();
        } else {
            $this->collection = $this->collection->splice($this->offset, $this->limit);
        }
    }

    protected function filterSince()
    {
        if ($since = $this->get('since')) {
            $date = array_get($this->context, $since, $since);
            $this->collection = $this->collection->removeBefore($date);
        }
    }

    protected function filterUntil()
    {
        if ($until = $this->get('until')) {
            $date = array_get($this->context, $until, $until);
            $this->collection = $this->collection->removeAfter($date);
        }
    }

    protected function sort()
    {
        if ($sort = $this->getSortOrder()) {
            $this->collection = $this->collection->multisort($sort);
        }
    }

    protected function getSortOrder()
    {
        if ($sort = $this->get('sort')) {
            return $sort;
        }

        return 'date:desc';
    }

    protected function paginate()
    {
        $this->paginated = true;

        // Keep track of how many items were in the collection before pagination chunks it up.
        $this->total_results = $this->collection->count();

        $page = (int) Request::get('page', 1);

        $this->offset = (($page - 1) * $this->limit) + $this->getInt('offset', 0);

        $items = $this->collection->slice($this->offset, $this->limit);

        $count = $this->collection->count() - $this->getInt('offset', 0);

        $last_page = (int) ceil($count / $this->limit);

        // Fix out of range pagination.
        if ($page > $last_page) {
            // ie. If there are 5 pages of results, and ?page=6 is
            // used, we'll act as though they entered ?page=5.
            $page = $last_page;
        } elseif ($page < 1) {
            // If for some reason the page is less than 1, make it 1.
            $page = 1;
        }

        $paginator = new LengthAwarePaginator($items, $count, $this->limit, $page);

        $paginator->setPath(URL::getCurrent());
        $paginator->appends(Request::all());

        $this->pagination_data = [
            'total_items'    => $count,
            'items_per_page' => $this->limit,
            'total_pages'    => $paginator->lastPage(),
            'current_page'   => $paginator->currentPage(),
            'prev_page'      => $paginator->previousPageUrl(),
            'next_page'      => $paginator->nextPageUrl(),
            'auto_links'     => $paginator->render(),
            'links'          => $paginator->render(new PaginationPresenter($paginator))
        ];

        $this->collection = $paginator->getCollection();
    }

}