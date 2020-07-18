<?php

namespace Statamic\Addons\Meerkat;

use Illuminate\Support\Collection;
use Statamic\Addons\Collection\CollectionTags;
use Statamic\Addons\Meerkat\Comments\CommentCollection;
use Statamic\Addons\Meerkat\Comments\Filters\FilterException;
use Statamic\Addons\Meerkat\Comments\Manager;
use Statamic\Addons\Meerkat\Comments\Stream;
use Statamic\Addons\Meerkat\Extend\ThemeFilters;
use Statamic\Addons\Meerkat\Helpers\CollectionTagHelpers;
use Statamic\Addons\Meerkat\UI\Gravatar;
use Statamic\API\Arr;
use Statamic\API\Config;
use Statamic\API\Crypt;
use Statamic\API\Form;
use Statamic\API\Parse;
use Statamic\API\User;
use Statamic\Data\DataCollection;

class MeerkatTags extends CollectionTags
{
    use CollectionTagHelpers,
        Gravatar;

    /**
     * The name of the Meerkcat formset.
     *
     * @var string
     */
    const MEERKAT_FORMSET = 'meerkat';

    /**
     * @var object
     */
    protected $errorBag;

    /**
     * @var CommentCollection
     */
    protected $collection;

    /**
     * The theme filters instance.
     *
     * @var ThemeFilters
     */
    private $meerkatThemeFilters;

    public function __construct($properties = [])
    {
        parent::__construct($properties);
        $meerkatThemeHelpers = site_path('/helpers/Meerkat.php');

        if (file_exists($meerkatThemeHelpers)) {
            require_once $meerkatThemeHelpers;
        }

        /** @var ThemeFilters meerkatThemeFilters */
        $this->meerkatThemeFilters = app(ThemeFilters::class);

        SettingsPatcher::loadMeerkatHelpers();
        SettingsPatcher::ensurePathsExist();
    }

    /**
     * The {{ meerkat }} tag
     *
     * @return string|array
     */
    public function index()
    {
        //
    }

    public function commentsEnabled()
    {
        return app(Manager::class)->areCommentsEnabled($this->context['date']);
    }

    /**
     * Maps to {{ meerkat:create }}
     *
     * @return string
     */
    public function create()
    {
        $data = [];
        $this->errorBag = $this->getErrorBag();

        if (!array_key_exists('attr', $this->parameters)) {
            $this->parameters['attr'] = '';
        }

        $this->parameters['attr'] = $this->parameters['attr'] . '|data-meerkat-form:comment-form';

        $html = $this->formOpen('socialize');
        $html .= '<input type="hidden" name="meerkat_context" value="' . $this->getHiddenContext() . '" />';

        if ($this->hasErrors()) {
            $data['error'] = $this->getErrors();
            $data['errors'] = $this->getErrorMessages();
        }

        if ($this->flash->exists('success')) {
            $data['success'] = true;
        }

        $data['fields'] = (Form::fields(self::MEERKAT_FORMSET));

        $data['old'] = old();

        $params = ['formset' => self::MEERKAT_FORMSET];

        if ($redirect = $this->get('redirect')) {
            $params['redirect'] = $redirect;
        }

        if ($error_redirect = $this->get('error_redirect')) {
            $params['error_redirect'] = $error_redirect;
        }

        $encryptedParams = Crypt::encrypt($params);

        $html .= '<input type="hidden" name="_params" value="' . $encryptedParams . '" />';

        $html .= $this->parse($data);

        $html .= '</form>';

        return $html;
    }

    /**
     * Get the errorBag from session
     *
     * @return object
     */
    private function getErrorBag()
    {
        if ($this->hasErrors()) {
            return session('errors')->getBag('form.' . self::MEERKAT_FORMSET);
        }
    }

    /**
     * Indicates if the form has errors.
     *
     * @return bool
     */
    private function hasErrors()
    {
        return (session()->has('errors'))
            ? session()->get('errors')->hasBag('form.' . self::MEERKAT_FORMSET)
            : false;
    }

    /**
     * Gets the context that new submissions should be associated with.
     *
     * @return string|null
     */
    private function getHiddenContext()
    {
        $sharing = data_get($this->context, 'meerkat_share_comments', null);

        if ($sharing !== null && is_array($sharing) && count($sharing) > 0) {
            return $sharing[0];
        }

        return data_get($this->context, 'page.id', null);
    }

    /**
     * Get an array of all the error messages, keyed by their input names.
     *
     * @return array
     */
    private function getErrors()
    {
        return array_combine($this->errorBag->keys(), $this->getErrorMessages());
    }

    /**
     * Get an array of all the error messages.
     *
     * @return array
     */
    private function getErrorMessages()
    {
        return $this->errorBag->all();
    }

    public function comments()
    {
        $stream = app(Manager::class)->getStream($this->getHiddenContext());
        $this->collection = $stream->getComments();

        $this->filterResponses();
        if ($this->collection->isEmpty()) {
            return null;
        }

        return $this->parseLoop($this->collection->setRecursiveCommentKey('comments')->toArray(true));
    }

    protected function filterResponses($limit = true)
    {
        $this->filterReplies();
        $this->filterUnpublished();
        $this->filterSince();
        $this->filterUntil();

        if (!$this->collection->isEmpty()) {
            $this->sort();
        }

        if ($limit) {
            $this->limit();
        }
    }

    protected function filterReplies()
    {
        if (!$this->getBool('flat', false)) {
            $this->collection = $this->collection->removeFirstLevelReplies();
        }
    }

    protected function filterUnpublished()
    {
        if (!$this->getBool(['unapproved', 'show_all', 'auto_publish'], false)) {
            $this->collection = $this->collection->removeUnpublished();
        }
    }

    public function designerMode()
    {
        return $this->getConfigBool('designer_mode', false);
    }

    /**
     * Renders the meerkat:all-comments Tag.
     *
     * @return array|string
     * @throws \Exception
     */
    public function allComments()
    {
        $this->collection = new CommentCollection();

        $userId = $this->get('user', "*current*");
        $context = $this->get('context', '*all*');

        if ($userId == '*current*') {
            $currentUser = User::getCurrent();

            if ($currentUser === null) {
                $this->collection = new CommentCollection();

                if ($this->collection->isEmpty()) {
                    return $this->parseNoResults();
                }

                return $this->output();
            } else {
                $userId = $currentUser->id();
            }
        }

        if ($context === '*all*') {
            $context = null;
        }

        $tempComments = app(Manager::class)->allComments(true);
        $filteredComments = [];

        foreach ($tempComments as $comment) {
            $postContext = $comment->get('context');

            if ($userId !== null) {
                $commentUser = $comment->user();

                if ($commentUser !== null && $commentUser->id() == $userId) {
                    if ($context !== null) {
                        if ($postContext == null) {
                            continue;
                        } else {
                            if ($postContext->id() !== $context) {
                                continue;
                            } else {
                                $filteredComments[] = $comment;
                            }
                        }
                    } else {
                        $filteredComments[] = $comment;
                        continue;
                    }
                }
            }

            if ($context === null) {
                if ($postContext !== null) {
                    $contextId = $postContext->id();
                    if ($contextId !== null && $contextId === $context) {
                        $filteredComments[] = $comment;
                    }
                }
            }
        }

        $this->collection = new CommentCollection($filteredComments);

        $this->filterResponses();

        $dynamicFilters = $this->getParam('filter', null);

        if ($dynamicFilters !== null) {
            // This method sets the internal filter.
            $this->processThemeFilters(collect($this->parameters), $dynamicFilters, $this->context, 'meerkat:all-comments');
        }

        if ($this->collection->isEmpty()) {
            return $this->parseNoResults();
        }

        return $this->output();
    }

    protected function output()
    {
        $as = $this->get('as');

        // Grouping by date requires some pretty different formatting, so
        // we'll want to catch this early on and do something separate.
        if ($this->get('group_by_date')) {
            if ($this->paginated) {
                // todo
                throw new \Exception("
                    Paginating entries grouped by date isn't currently supported.
                    Let us know that you want it.
                ");
            } else {
                return $this->groupByDate();
            }

        } else {
            if ($this->paginated) {
                // Paginated? we need to nest inside a scope key
                $as = $as ?: 'comments';

                $data = [$as => $this->collection->setRecursiveCommentKey($as)->toArray(true)];


                $data['paginate'] = $this->pagination_data;

                $data = array_merge($data, $this->getCollectionMetaData());

                return $this->parseComments($data, [], $as);
            } else {
                // Not paginated, but we can still nest inside a scope key if they have specified to.
                if ($as) {
                    $data =
                        array_merge(
                            [$as => $this->collection->setRecursiveCommentKey($as)->toArray(true)],
                            $this->getCollectionMetaData()
                        );

                    return $this->parseComments($data, [], $as);
                } else {
                    // Add the meta data (total_results, etc) into each iteration.
                    $meta = $this->getCollectionMetaData();
                    $data = collect($this->collection->toArray(true))->map(function ($item) use ($meta) {
                        return array_merge($item, $meta);
                    })->all();


                    return $this->parseLoop($data, false, []);
                }
            }
        }
    }

    /**
     * Parse the tag pair contents with scoped variables
     *
     * @param array $data Data to be parsed into template
     * @param array $context Contextual variables to also use
     * @param string $collectionName The collection name.
     * @return string
     */
    protected function parseComments($data = [], $context = [], $collectionName = 'comments')
    {
        if ($this->trim) {
            $this->content = trim($this->content);
        }

        $context = array_merge($context, $this->context);

        // '/\{\{\s*\*recursive\s*('.$this->variableRegex.')\*\s*\}\}/ms';
        $nestedTagRegex = '/\{\{\s*' . $collectionName . '\s*\}\}.*?\{\{\s*\/' . $collectionName . '\s*\}\}/ms';
        preg_match($nestedTagRegex, $this->content, $match);

        $subKey = 'meerkat_comments_tags_' . md5(time());

        if ($match && count($match) > 0) {
            $nestedCommentsString = $match[0];

            // Remove tag pair from the original template.
            $this->content = preg_replace($nestedTagRegex, $subKey, $this->content);

            // Create some regexes to find the opening and closing comments.
            $openingTagRegex = '/\{\{\s*' . $collectionName . '\s*\}\}/ms';
            $closingTagRegex = '/\{\{\s*\/' . $collectionName . '\s*\}\}/ms';

            // We need to remove the opening and closing tag pairs from the template.
            $nestedCommentsString = preg_replace($openingTagRegex, '', $nestedCommentsString);
            $nestedCommentsString = preg_replace($closingTagRegex, '', $nestedCommentsString);

            $comments = $data[$collectionName];

            for ($i = 0; $i < count($comments); $i++) {
                $comment = $comments[$i];
                $comment['comment'] = str_replace('{', '&#123;', $comment['comment']);
                $comment['comment'] = str_replace('}', '&#125;', $comment['comment']);
                $comments[$i] = $comment;
            }

            $data[$collectionName] = $comments;

            $tempContent = Parse::templateLoop($nestedCommentsString, $data[$collectionName], true, $context);

            // At this point, we need to render the template without the Meerkat comments tags.
            $template = Parse::template($this->content, $this->addScope($data), $context);
            return str_replace($subKey, $tempContent, $template);
        }

        return Parse::template($this->content, $this->addScope($data), $context);
    }

    /**
     * Add the provided $data to its own scope
     *
     * @param array|\Statamic\Data\DataCollection $data
     * @return mixed
     */
    private function addScope($data)
    {
        if ($scope = $this->getParam('scope')) {
            $data = Arr::addScope($data, $scope);
        }

        if ($data instanceof DataCollection) {
            $data = $data->toArray();
        }

        return $data;
    }

    /**
     * Renders the meerkat:responses tag.
     *
     * @return array|string
     * @throws \Exception
     */
    public function responses()
    {
        $withTrashed = $this->getBool('show_hidden');

        /** @var Stream $stream */
        $stream = app(Manager::class)->getStream($this->getHiddenContext());

        if ($withTrashed) {
            $stream->withTrashed();
        } else {
            $stream->withoutTrashed();
        }

        $designerMode = $this->getConfigBool('designer_mode', false);

        if (!$designerMode) {
            $this->collection = $stream->getComments();
        } else {
            $this->collection = $stream->getDesignerModeComments();
        }

        $this->filterResponses();

        $dynamicFilters = $this->getParam('filter', null);

        if ($dynamicFilters !== null) {
            // This method sets the internal collection.
            $this->processThemeFilters(collect($this->parameters), $dynamicFilters, $this->context, 'meerkat:responses');
        }

        if ($this->collection->isEmpty()) {
            return $this->parseNoResults();
        }

        return $this->output();
    }

    /**
     * Processes all theme filters for the current context.
     *
     * @param Collection $params The template parameters, if available.
     * @param string $filters The Meerkat filters.
     * @param null $context The Meerkat context, if any.
     * @param string $tagContext The filter tag context.
     * @throws \Exception
     */
    private function processThemeFilters($params, $filters, $context = null, $tagContext = '')
    {
        $isDebugEnabled = Config::get('debug.debug', false);

        // Remap filters.
        $filters = $this->meerkatThemeFilters->getFilterMap($filters);
        $statamicUser = User::getCurrent();

        $this->meerkatThemeFilters->setUser($statamicUser);

        // Create a temporary collection, containing all the theme properties of the comments.
        $themeFilterComments = $this->collection->toArray(true);

        $filters = explode('|', $filters);
        $idsToKeep = [];

        foreach ($filters as $filter) {
            if ($this->meerkatThemeFilters->hasFilter(trim($filter))) {
                try {
                    $filterResults = $this->meerkatThemeFilters->runFilter($filter, new Collection($themeFilterComments), $params, $context, $tagContext);

                    if ($filterResults !== null && $filterResults instanceof  Collection) {
                        //$this->collection = $filterResults;
                        $idsToKeep = $filterResults->pluck('id')->toArray();

                        if (count($idsToKeep) == 0) {
                            break;
                        } else {
                            $themeCollection = new Collection($themeFilterComments);

                            // Create a new collection for the next comment filter.
                            $themeFilterComments = $themeCollection->reject(function ($comment) use ($idsToKeep) {
                                $thisId = $comment['id'];

                                return in_array($thisId, $idsToKeep) == false;
                            })->toArray();
                        }
                    }
                } catch (\Exception $e) {
                    if ($isDebugEnabled) {
                        throw $e;
                    }
                }
            } else {
                throw new FilterException($filter.' Meerkat filter could not be found.');
            }
        }

        // Re-create the main collection (effectively throwing away any
        //modifications a filter might have tried). Keep this clean.
        $this->collection = $this->collection->filter(function ($comment) use ($idsToKeep) {
            return in_array($comment->id(), $idsToKeep);
        });
    }

    public function cpLink()
    {
        $commentId = $this->context['id'];

        return '<a name="comment-' . $commentId . '"></a>';
    }

    public function repliesTo()
    {
        // Hi there, source divers! Now you know why the version() method
        // has been included in the API! As they say: the more you know
        $version = $this->api('Meerkat')->version();
        $javaScriptLocation = cp_resource_url('../addons/Meerkat/js/reply-to.js?v=' . $version);

        return '<script type="text/javascript" src="' . $javaScriptLocation . '"></script>';
    }

    public function participants()
    {
        /** @var Stream $stream */
        $stream = app(Manager::class)->getStream($this->getHiddenContext());
        $this->collection = $stream->getParticipants($this->get('using', ['email', 'name']));

        if ($this->collection->isEmpty()) {
            return $this->parseNoResults();
        }

        return $this->output();
    }

}
