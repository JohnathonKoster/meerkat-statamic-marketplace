<?php

namespace Statamic\Addons\Meerkat\Routes;

use Statamic\API\URL;
use Illuminate\Support\Facades\Input;
use Statamic\Addons\Meerkat\MeerkatAPI;
use Statamic\Addons\Meerkat\Comments\Stream;
use Statamic\Addons\Meerkat\Comments\Factory;
use Statamic\Addons\Meerkat\Comments\Comment;
use Statamic\Addons\Meerkat\Comments\Manager;
use Illuminate\Pagination\LengthAwarePaginator;
use Statamic\Addons\Meerkat\Comments\Spam\Guard;
use Statamic\Addons\Meerkat\Comments\CommentCollection;
use Statamic\Addons\Meerkat\Comments\Metrics\CommentMetrics;

trait APIRoutes
{

    /**
     * Gets the total comment count.
     *
     * @param  Stream $stream
     * @return \Illuminate\Http\JsonResponse
     */
    public function getApiCommentCount(Stream $stream)
    {
        return response()->json([
            'time' => time(),
            'count' => $stream->count()
        ]);
    }

    /**
     * Returns all streams.
     *
     * @param  Manager $manager
     * @return array
     */
    public function getApiStreams(Manager $manager)
    {
        $columns = [
            [
                'label' => 'context',
                'field' => 'context',
                'translation' => 'Content'
            ],
            [
                'label' => 'published_count',
                'field' => 'published_count',
                'translation' => 'Approved Comments'
            ],
            [
                'label' => 'pending_count',
                'field' => 'pending_count',
                'translation' => 'Pending'
            ],
            [
                'label' => 'spam_count',
                'field' => 'spam_count',
                'translation' => 'Spam'
            ]
        ];

        $items = $manager->getStreams()->map(function ($stream) {
            $data = $stream->toArray();
            $data['edit_url'] = URL::assemble('/' . CP_ROUTE, 'addons', 'meerkat', 'comments', $data['context_id']);
            return $data;
        })->toArray();

        return compact('columns', 'items', 'tableOptions');
    }

    /**
     * Returns all comments for a given stream.
     *
     * @param Manager $manager
     * @return array
     */
    public function getApiStreamComments(Manager $manager)
    {
        $context = Input::get('context');

        $form = MeerkatAPI::getForm();

        $columns = collect($form->columns())->map(function ($val, $column) {
            return ['label' => $column, 'field' => $column, 'translation' => $val];
        })->values()->reverse()->push([
            'label' => 'datestring',
            'field' => 'datestamp'
        ])->reverse();

        $items = $manager->getStream($context)->getComments()->map(function ($comment) {
            $data = $comment->toArray();
            $data['datestring'] = (string)$data['date'];
            $data['datestamp'] = $data['date']->timestamp;
            return $data;
        });

        return compact('columns', 'items', 'tableOptions');
    }

    /**
     * Gets the statistics for the comment items.
     *
     * @param  array|CommentCollection $items
     * @return array
     */
    private function getStats($items)
    {
        return with(new CommentMetrics)->setComments($items)->toArray();
    }

    /**
     * Gets the comments based on the request parameters.
     *
     * This API endpoint accepts the following:
     *
     *   - filter<string>: Allows you to filter the returned comments.
     *     - values:
     *       - all:      Returns all comments.
     *       - pending:  Returning all unpublished comments.
     *       - spam:     Returns all comments flagged as spam.
     *       - approved: Returns all published comments.
     *
     *   - paginated<bool>: Determines if results should be paginated.
     *     - values:
     *       - true:  Results will be paginated.
     *       - false: Results will not be paginated.
     *
     *   - perPage<int>: The number of results per page when paginated.
     *     - values:
     *       - <int|10>: The number of results per page (default 10).
     *
     *   - page<int>: The page number to return results for.
     *     - values:
     *       - <int|1>: The page number to return results for (default 1).
     * 
     *   - stream<string>: The ID of the comment stream to load.
     *     - values:
     *       - <string|1>: The ID of the comment stream to load.
     * 
     *   - streamFor<int>: Supply a comment ID here to load it's stream.
     *     - values:
     *       - <int|1>:   The comment ID.
     *
     * @param Manager $manager
     * @return array
     */
    public function getApiComments(Manager $manager)
    {
        $currentLocale = meerkat_get_config('cp.locale', 'en');

        $columns = [
            [
                'label' => 'name',
                'field' => 'name',
                'translation' => meerkat_trans('comments.table_author', [], 'messages', $currentLocale)
            ],
            [
                'label' => 'comment',
                'field' => 'comment',
                'translation' => meerkat_trans('comments.table_comment', [], 'messages', $currentLocale)
            ]
        ];

        $streamFilter = Input::get('stream', null);

        if ($streamFilter !== null && Input::get('streamFor') !== null) {
            $streamFor = Input::get('streamFor');
            if (trim($streamFor) == intval($streamFor)) {
                $comment = $this->api('Meerkat')->findOrFail($streamFor);
                $streamFilter = $comment->getStreamName();
            }
        }

        if ($streamFilter == null) {
            $items = $manager->allComments(true);
        } else {
            $items = $manager->getStreamComments($streamFilter, true);
        }

        $statistics = $this->getStats($items);

        $filter = Input::get('filter', 'all');

        $validFilters = ['all', 'pending', 'spam', 'approved'];

        if (!in_array($filter, $validFilters)) {
            $filter = 'all';
        }

        // Apply our filter.
        switch ($filter) {
            case 'pending':
                $items = $items->filter(function (Comment $comment) {
                   return !$comment->approved();
                });
                break;
            case 'spam':
                $items = $items->filter(function (Comment $comment) {
                   return $comment->isSpam();
                });
                break;
            case 'approved':
                $items = $items->filter(function (Comment $comment) {
                   return $comment->approved();
                });
                break;
        }

        $total = $statistics['all'];

        if ($filter !== 'all' && in_array($filter, $validFilters)) {
            $total = $items->count();
        }

        $perPage = null;
        $page = null;

        // Apply pagination.
        if (Input::get('paginate', false) === 'true') {
            $perPage = intval(Input::get('perPage', 10));
            $page    = intval(Input::get('page', 1));
            $offset  = ($page * $perPage) - $perPage;

            // Limit the items.
            $items = $items->slice($offset, $perPage, true);
        }

        $items = $items->map(function (Comment $comment) {
            return Factory::makeApiData($comment);
        });

        $items = $items->map(function ($data) use ($items) {
            $data['comment_count'] = collect($items)->where('in_response_to_url', $data['in_response_to_url'])->count();
            return $data;
        });

        if (Input::get('paginate', false) === 'true') {
            $paginator = new LengthAwarePaginator(
                $items,
                $total,
                $perPage,
                $page,
                ['path' => request()->url(), 'query' => request()->query()]
            );

            $items = $paginator->toArray();
        }

        return compact('columns', 'items', 'statistics');
    }

    /**
     * Rechecks all comments for spam.
     *
     * @param  Manager $manager
     * @param  Guard $guard
     * @return array
     */
    public function postCheckForSpam(Manager $manager, Guard $guard)
    {
        $items = $manager->allComments();

        $items->each(function(Comment $comment) use (&$guard) {
            if ($guard->process($comment->getStoredData())) {
                // Update the spam and published stats for the comment.
                $comment['published'] = false;
                $comment['spam'] = true;
                $comment->save();
            }
        });

        $statistics = $this->getStats($items);

        if (!request()->ajax()) {
            $redirectTo = request('redirect', null);

            if ($redirectTo == 'cp') {
                return redirect(route('cp'));
            }
        }

        return compact('statistics');
    }

}