<?php

namespace Statamic\Addons\Meerkat;

use Statamic\API\Arr;
use Statamic\API\Form;
use Statamic\API\Parse;
use Statamic\API\Crypt;
use Statamic\Data\DataCollection;
use Statamic\Addons\Meerkat\UI\Gravatar;
use Statamic\Addons\Meerkat\Comments\Stream;
use Statamic\Addons\Meerkat\Comments\Manager;
use Statamic\Addons\Collection\CollectionTags;
use Statamic\Addons\Meerkat\Comments\CommentCollection;
use Statamic\Addons\Meerkat\Helpers\CollectionTagHelpers;

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
     * The {{ meerkat }} tag
     *
     * @return string|array
     */
    public function index()
    {
        //
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

        if (! array_key_exists('attr', $this->parameters)) {
            $this->parameters['attr'] = '';
        }
        
        $this->parameters['attr'] = $this->parameters['attr'].'|data-meerkat-form:comment-form';
        
        $html = $this->formOpen('socialize');
        $html .= '<input type="hidden" name="meerkat_context" value="'.$this->getHiddenContext().'" />';

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

        $html .= '<input type="hidden" name="_params" value="'. $encryptedParams .'" />';

        $html .= $this->parse($data);

        $html .= '</form>';

        return $html;
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

    public function designerMode()
    {
        return $this->getConfigBool('designer_mode', false);
    }

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
        // $designerMode = false;

        if (!$designerMode) {
            $this->collection = $stream->getComments();
        } else {
            $this->collection = $stream->getDesignerModeComments();
        }

        $this->filterResponses();

        if ($this->collection->isEmpty()) {
            return $this->parseNoResults();
        }

        return $this->output();
    }

    public function cpLink()
    {
        $commentId = $this->context['id'];

        return '<a name="comment-'.$commentId.'"></a>';
    }
    
    public function repliesTo()
    {
        // Hi there, source divers! Now you know why the version() method
        // has been included in the API! As they say: the more you know
        $version = $this->api('Meerkat')->version();
        $javaScriptLocation = cp_resource_url('../addons/Meerkat/js/reply-to.js?v='.$version);
        
        return '<script type="text/javascript" src="'.$javaScriptLocation.'"></script>';
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
     * Parse the tag pair contents with scoped variables
     *
     * @param array $data     Data to be parsed into template
     * @param array $context  Contextual variables to also use
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
        $nestedTagRegex = '/\{\{\s*'.$collectionName.'\s*\}\}.*?\{\{\s*\/'.$collectionName.'\s*\}\}/ms';
        preg_match($nestedTagRegex, $this->content, $match);

        $subKey = 'meerkat_comments_tags_'.md5(time());

        if ($match && count($match) > 0) {
            $nestedCommentsString = $match[0];

            // Remove tag pair from the original template.
            $this->content = preg_replace($nestedTagRegex, $subKey, $this->content);

            // Create some regexes to find the opening and closing comments.
            $openingTagRegex = '/\{\{\s*'.$collectionName.'\s*\}\}/ms';
            $closingTagRegex = '/\{\{\s*\/'.$collectionName.'\s*\}\}/ms';

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
                    $data = [
                        array_merge(
                            [$as => $this->collection->setRecursiveCommentKey($as)->toArray(true)],
                            $this->getCollectionMetaData()
                        )
                    ];
                } else {
                    // Add the meta data (total_results, etc) into each iteration.
                    $meta = $this->getCollectionMetaData();
                    $data = collect($this->collection->toArray(true))->map(function ($item) use ($meta) {
                        return array_merge($item, $meta);
                    })->all();
                }

                return $this->parseLoop($data);
            }
        }
    }

    protected function filterResponses($limit = true)
    {
        $this->filterReplies();
        $this->filterUnpublished();
        $this->filterSince();
        $this->filterUntil();

        if (! $this->collection->isEmpty()) {
            $this->sort();
        }

        if ($limit) {
            $this->limit();
        }
    }

    protected function filterReplies()
    {
        if (! $this->getBool('flat', false)) {
            $this->collection = $this->collection->removeFirstLevelReplies();
        }
    }

    protected function filterUnpublished()
    {
        if (! $this->getBool(['unapproved', 'show_all', 'auto_publish'], false)) {
            $this->collection = $this->collection->removeUnpublished();
        }
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

    /**
     * Indicates if the form has errors.
     * 
     * @return bool
     */
    private function hasErrors()
    {
        return (session()->has('errors'))
               ? session()->get('errors')->hasBag('form.'.self::MEERKAT_FORMSET)
               : false;
    }

    /**
     * Get the errorBag from session
     * 
     * @return object
     */
    private function getErrorBag()
    {
        if ($this->hasErrors()) {
            return session('errors')->getBag('form.'.self::MEERKAT_FORMSET);
        }
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

}
