<?php

namespace Statamic\Addons\Meerkat;

use Statamic\Addons\Meerkat\Comments\Metrics\CommentMetrics;
use Statamic\Addons\Meerkat\Permissions\AccessManager;
use Statamic\API\Str;
use Statamic\API\Data;
use Statamic\API\Crypt;
use Statamic\API\User;
use Statamic\API\Helper;
use Statamic\API\Content;
use Statamic\API\Request;
use Illuminate\Http\Response;
use Statamic\Extend\Extensible;
use Statamic\Extend\Controller;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Input;
use Statamic\Exceptions\PublishException;
use Statamic\Exceptions\HoneypotException;
use Statamic\Addons\Meerkat\Markdown\Parser;
use Statamic\Addons\Meerkat\Comments\Stream;
use Statamic\Addons\Meerkat\Comments\Factory;
use Statamic\Addons\Meerkat\Comments\Manager;
use Statamic\Addons\Meerkat\Routes\APIRoutes;
use Statamic\Addons\Meerkat\Comments\Comment;
use Statamic\Extend\Contextual\ContextualFlash;
use Statamic\Addons\Meerkat\Routes\ExportRoutes;
use Statamic\Addons\Meerkat\Routes\ProtectsRoutes;
use Statamic\Addons\Meerkat\Comments\CommentManager;

class MeerkatController extends Controller
{
    use Extensible, APIRoutes, ExportRoutes, ProtectsRoutes, MeerkatHelpers;

    protected $streamManager;
    protected $formFlash;

    protected $protectedRoutes = [
        'index',
        'getComments',
        'getApiCommentCount',
        'getApiStreams',
        'getApiStreamComments',
        'getApiComments',
        'delete',
        'markCommentsAsSpam',
        'markCommentsAsNotSpam',
        'approveComments',
        'unApproveComments',
        'updateComment',
        'postCheckForSpam',
        'getExport',
        'getCounts'
    ];

    protected $secondaryProtected = [
        '/',
        'comments',
        'api-comment-count',
        'api-streams',
        'api-stream-comments',
        'api-comments'
    ];

    protected $accessManager = null;

    public function __construct(Manager $streamManager)
    {
        $this->addon_name = 'Meerkat';
        $this->streamManager = $streamManager;
        $this->formFlash = new ContextualFlash('Form');
        $this->protectRoutes();
        $permissions = $this->getConfig('permissions');

        $accessManager = new AccessManager();
        $accessManager->setUser(User::getCurrent());
        $accessManager->setPermissions($this->getConfig('permissions'));
        $accessManager->resolve();

        $this->accessManager = $accessManager;
    }

    /**
     * Maps to your route definition in routes.yaml
     *
     * @return Illuminate\Http\Response
     */
    public function index()
    {
        if (!$this->accessManager->canViewComments()) {
            if (request()->ajax()) {
                return response('Unauthorized.', 401);
            } else {
                abort(403);
                return;
            }
        }

        return $this->view('streams.index', [
            'title' => $this->meerkatTrans('comments.comments'),
            'form' => MeerkatAPI::getForm(),
            'filter' => Input::get('filter', 'all'),
            'hideManagement' => false,
            'cpPath' => $this->meerkatCpPath()
        ]);
    }

    /**
     * Deletes the specified comments.
     *
     * @param  CommentManager $manager
     * @return array
     */
    public function deleteComments(CommentManager $manager)
    {
        if (!$this->accessManager->canRemoveComments()) {
            if (request()->ajax()) {
                return response('Unauthorized.', 401);
            } else {
                abort(403);
                return;
            }
        }

        $comments = Helper::ensureArray(Input::get('ids', []));
        $commentsRemoved = $manager->removeComments($comments);

        return [
            'success' => true,
            'removed' => $commentsRemoved
        ];
    }

    /**
     * Makes the specified comments as spam.
     *
     * @param  CommentManager $manager
     * @return array
     */
    public function markCommentsAsSpam(CommentManager $manager)
    {
        if (!$this->accessManager->canReportAsSpam()) {
            if (request()->ajax()) {
                return response('Unauthorized.', 401);
            } else {
                abort(403);
                return;
            }
        }

        $comments = Helper::ensureArray(Input::get('ids', []));

        $wasSaved = false;
        $wasSuccess = true;
        $didSend = false;
        $errors = [];

        try {
            $result = $manager->markCommentsAsSpam($comments);

            if ($result['wasSuccess'] === false) {
                $wasSuccess = false;
            }

            if ($result['wasSaved']) {
                $wasSaved = true;
            }

            if ($result['didSend']) {
                $didSend = true;
            }

            $errors = array_merge($errors, $result['errors']);

        } catch (\Exception $e) {
            $errors[] = $e->getMessage();
        }

        return [
            'comment_saved' => $wasSaved,
            'submit_success' => $wasSuccess,
            'specimen_sent' => $didSend,
            'marked' => $comments,
            'errorMessage' => $errors
        ];
    }

    /**
     * Marks the specified as not spam (ham).
     *
     * @param  CommentManager $manager
     * @return array
     */
    public function markCommentsAsNotSpam(CommentManager $manager)
    {
        if (!$this->accessManager->canReportAsHam()) {
            if (request()->ajax()) {
                return response('Unauthorized.', 401);
            } else {
                abort(403);
                return;
            }
        }

        $comments = Helper::ensureArray(Input::get('ids', []));

        $wasSaved = false;
        $wasSuccess = true;
        $didSend = false;
        $errors = [];

        try {
            $result = $manager->markCommentsAsNotSpam($comments);

            if ($result['wasSuccess'] === false) {
                $wasSuccess = false;
            }

            if ($result['wasSaved']) {
                $wasSaved = true;
            }

            if ($result['didSend']) {
                $didSend = true;
            }

            $errors = array_merge($errors, $result['errors']);

        } catch (\Exception $e) {
            $errors = $e->getMessage();
        }

        return [
            'comment_saved' => $wasSaved,
            'submit_success' => $wasSuccess,
            'specimen_sent' => $didSend,
            'marked' => $comments,
            'errorMessage' => $errors
        ];
    }

    /**
     * Approves the specified comments.
     *
     * @param  CommentManager $manager
     * @return array
     */
    public function approveComments(CommentManager $manager)
    {
        if (!$this->accessManager->canApproveComments()) {
            if (request()->ajax()) {
                return response('Unauthorized.', 401);
            } else {
                abort(403);
                return;
            }
        }

        $comments = Helper::ensureArray(Input::get('ids', []));

        $approveErrorMessage = null;

        try {
            $manager->approveComments($comments);
            $approveSucceeded = true;
        } catch (\Exception $e) {
            $approveErrorMessage = $e->getMessage();
            $approveSucceeded = false;
        }

        return [
            'success' => $approveSucceeded,
            'approved' => $comments,
            'errorMessage' => $approveErrorMessage
        ];
    }

    /**
     * Marks the specified comments as unapproved, or un-publishes them.
     *
     *
     * @param  CommentManager $manager
     * @return array
     */
    public function unApproveComments(CommentManager $manager)
    {
        if (!$this->accessManager->canUnApproveComments()) {
            if (request()->ajax()) {
                return response('Unauthorized.', 401);
            } else {
                abort(403);
                return;
            }
        }

        $comments = Helper::ensureArray(Input::get('ids', []));

        $unapproveErrorMessage = null;

        try {
            $manager->unApproveComments($comments);
            $unapproveSucceeded = true;
        } catch (\Exception $e) {
            $unapproveSucceeded = false;
            $unapproveErrorMessage = $e->getMessage();
        }

        return [
            'success' => $unapproveSucceeded,
            'unapproved' => $comments,
            'errorMessage' => $unapproveErrorMessage
        ];
    }

    public function postDelete(CommentManager $manager)
    {
        $currentStatamicUser = User::getCurrent();

        if ($currentStatamicUser == null) {
            if (request()->ajax()) {
                return response('Unauthorized.', 401);
            } else {
                abort(403);
                return;
            }
        }

        $comments = Helper::ensureArray(Input::get('ids', []));

        if (count($comments) > 0) {
            $comments = [
                $comments[0]
            ];
        }

        if (count($comments) == 1) {
            $commentToRemove = $manager->findOrFail($comments[0]);

            $authorizedUserId = $commentToRemove->get('authenticated_user');

            if ($authorizedUserId != $currentStatamicUser->id()) {
                if (request()->ajax()) {
                    return response('Unauthorized.', 401);
                } else {
                    abort(403);
                    return;
                }
            }

            $commentsRemoved = $manager->removeComments($comments);

            return [
              'success' => true,
              'removed' => []
            ];
        }

        return [
            'success' => false,
            'removed' => []
        ];
    }

    public function postUpdate(CommentManager $manager)
    {
        $currentStatamicUser = User::getCurrent();

        if ($currentStatamicUser == null) {
            if (request()->ajax()) {
                return response('Unauthorized.', 401);
            } else {
                abort(403);
                return;
            }
        }

        // Do some checks to make sure the comment
        // is owned by the current Statamic user.
        $comments = array_slice(Helper::ensureArray(Input::get('ids'), []), 0, 1);

        if (count($comments) > 0) {
            $comment = $comments[0];

            $commentToUpdate = $manager->findOrFail($comment);
            $authorizedUserId = $commentToUpdate->get('authenticated_user');

            if ($authorizedUserId != $currentStatamicUser->id()) {
                if (request()->ajax()) {
                    return response('Unauthorized.', 401);
                } else {
                    abort(403);
                    return;
                }
            }
        }

        return $this->updateComment($manager, true);
    }

    /**
     * Updates the specified comments.
     *
     * @param  CommentManager $manager
     * @return array
     */
    public function updateComment(CommentManager $manager, $allowOverride = false)
    {
        if ($allowOverride == false && !$this->accessManager->canEditComments()) {
            if (request()->ajax()) {
                return response('Unauthorized.', 401);
            } else {
                abort(403);
                return;
            }
        }

        // A dirty little hack to ensure that we are only updating
        // one comment at a time. This way, we don't have to do
        // anymore changes on Meerkat's internal API to make
        // sure it can handle both array and literal input.
        $comments = array_slice(Helper::ensureArray(Input::get('ids'), []), 0, 1);

        $newComment = Input::get('comment');

        $errorMessage = null;
        $success = false;

        if (count($comments) == 0) {
            $success = false;
            $errorMessage = meerkat_trans('errors.edit_input_no_comments');

            // We need to set this so that any consumers can reliably
            // check against, and use, this value in their apps.
            $comments[0] = null;
        } elseif (mb_strlen($newComment) == 0) {
            $success = false;
            $errorMessage = meerkat_trans('errors.edit_input_invalid_comment');
        }

        // Only entry the try {} block if the $errorMessage is null.
        // When the $errorMessage is null, this means that our
        // quick sanity checks have all passed and we can
        // attempt to update the comment using the API.
        if ($errorMessage == null) {
            try {
                $manager->setCommentContent($comments[0], $newComment);
                $success = true;
            } catch (\Exception $e) {
                $success = false;
                $errorMessage = $e->getMessage();
            }
        }

        return [
            'success' => $success,
            'updated' => $comments[0],
            'parsedContent' => Parser::parseCommentContent($newComment),
            'originalMarkdown' => $newComment,
            'errorMessage' => $errorMessage
        ];
    }

    public function getComments($contextStream)
    {
        if (!$this->accessManager->canViewComments()) {
            if (request()->ajax()) {
                return response('Unauthorized.', 401);
            } else {
                abort(403);
                return;
            }
        }

        /** @var Manager $manager */
        $manager = app(Manager::class);
        $stream = $manager->getStream($contextStream);
        $form = MeerkatAPI::getForm();
        $form->limitSubmissions($contextStream);

        $context = $stream->context();

        return $this->view('streams.show', [
            'title' => 'Comments',
            'stream' => $stream->toArray(),
            'form' => $form,
            'context' => $context
        ]);
    }

    private function isAuthenticatedUser($submittedEmail)
    {
        if (auth()->user() !== null && auth()->user()->get('email') == $submittedEmail) {
            return true;
        }
        
        return false;
    }

    private function getDesignerModePostResponse()
    {
        $comment = new Comment;
        $comment->form(MeerkatAPI::getForm());
        $comment->unguard();
        
        $tempId = time();

        $comment->id($tempId);

        $requestData = request()->all();

        $entry = Data::find(array_get($requestData, 'meerkat_context'));

        $ids = array_get($requestData, 'ids', null);

        $commentData = [
            'name'    => array_get($requestData, 'name', ''),
            'email'   => array_get($requestData, 'email', ''),
            'comment' => markdown(array_get($requestData, 'comment', '')),
            'id'      => $tempId,
            'context' => $entry
        ];

        if ($ids != null) {
            $commentData[Stream::INTERNAL_RESPONSE_ID] = $ids;
        }

        $comment->data($commentData);

        if (request()->ajax()) {
            $commentApiData = Factory::makeApiData($comment);

            if ($ids != null) {
                $commentApiData['parent_comment_id'] = $ids;
            }

            return response([
                'success'    => true,
                'submission' => $commentApiData
            ]);
        }

        $params = array_get($requestData, '_params', []);
        $redirect = array_get($params, 'redirect');

        $response = ($redirect) ? redirect($redirect) : back();

        $this->formFlash->put('success', true);
        $this->formFlash->put('submission', $comment);

        return $response;
    }

    public function postSocialize()
    {
        $designerMode = $this->getConfigBool('designer_mode', false);
        
        if ($designerMode) {
            // If we are in designer mode, let's simply say everything went fine.
            return $this->getDesignerModePostResponse();
        }

        $fields = Request::all();
        $authenticatedRequest = false;
        $isReply = false;
        $replyingTo = null;

        if (!request()->ajax()) {
            $params = Request::input('_params');
            $params = Crypt::decrypt($params);
            unset($fields['_params']);
        } else {
            $params = [];
        }

        $context = '';

        if (isset($fields['ids']) && !empty($fields['ids'])) {
            if (is_array($fields['ids']) && count($fields['ids']) > 0) {
                $replyingTo = $fields['ids'][0];
            } else {
                $replyingTo = $fields['ids'];
            }

            if (mb_strlen(trim($replyingTo)) == 0) {
                $isReply = false;
            } else {
                $isReply = true;
            }
        }

        if (isset($fields['ids'])) {
            unset($fields['ids']);
        }

        if (isset($fields['meerkat_context'])) {
            $context = $fields['meerkat_context'];
        } elseif ($replyingTo != null) {
            $comments = app(CommentManager::class)->getComments($replyingTo);

            if ($comments->count() > 0) {
                $commentContext = $comments->first()->get('context');

                if ($commentContext != null) {
                    $context = $commentContext->id();
                }
            }
        }

        $form = MeerkatAPI::getForm();

        $submission = $form->createSubmission();

        // The request came from an authenticated user.
        if (auth()->user() !== null) {
            $user = auth()->user();

            if ((!request()->ajax() && auth()->user()->get('email') === $fields['email']) || request()->ajax()) {
                foreach ($this->getConfig('form_user_fields') as $formField => $userMapping) {
                    $fields[$formField] = $user->get($userMapping);
                }
            }

            $authenticatedRequest = true;
        }

        try {
            $submission->data($fields);
        } catch (PublishException $e) {
            $this->emitEvent('comment.attach.failed', $e);
            return $this->formFailure($params, $e->getErrors(), MeerkatTags::MEERKAT_FORMSET);
        } catch (HoneypotException $e) {
            return $this->formSuccess($params, $submission);
        }

        $entry = Content::find($context);

        if (!isset($fields['email']) && auth()->user() !== null && auth()->user()->isSuper()) {
            $fields['email'] = auth()->user()->get('email');
        }

        if (!$this->isAuthenticatedUser($fields['email']) &&
            !$this->streamManager->areCommentsEnabled($entry->date())) {
            $errors['*'] = $this->trans('comments.disabled');
            return $this->formFailure($params, $errors, MeerkatTags::MEERKAT_FORMSET);
        }

        // Get the Meerkat form.
        $form = MeerkatAPI::getForm();

        $submission = $form->createSubmission();

        try {
            $submission->data($fields);
        } catch (PublishException $e) {
            $this->emitEvent('comment.attach.failed', $e);
            return $this->formFailure($params, $e->getErrors(), MeerkatTags::MEERKAT_FORMSET);
        } catch (HoneypotException $e) {
            return $this->formSuccess($params, $submission);
        }


        try {
            list($errors, $submission) = $this->runCreatingEvent($submission);
        } catch (\Exception $e) {
            // Protect against configuration or third-parties from breaking the submission.
            $errors['creating'][] = $this->trans('errors.comments_create_reply_validation');

            $exceptionMessage = $e->getMessage();
            $trace = $e->getTrace();

            if ($trace != null && is_array($trace) && count($trace) > 0) {
                foreach ($trace as $item) {
                    if (array_key_exists('file', $item)) {
                        $file = $item['file'];

                        $file = str_replace('\\', '/', $file);
                        if (Str::contains(strtolower($file), 'site/addons')) {
                            $file = substr($file, strpos($file, 'site/addons'));
                            $exceptionMessage .= ' '.$this->trans('errors.error_in', [
                                    'location' => $file
                                ]);
                            break;
                        }
                    }
                }
            }

        }

        $authUserByPassCaptcha = $this->getConfig('captcha_auth_bypass', true);

        if ($authenticatedRequest && $authUserByPassCaptcha) {
            if ($errors) {
                if (array_key_exists('captcha', $errors)) {
                    unset($errors['captcha']);
                }
            }
        }

        if ($errors) {
            return $this->formFailure($params, $errors, MeerkatTags::MEERKAT_FORMSET);
        }

        if ($isReply) {
            $stream = $this->streamManager->getEmptyStream();
            $stream->attachReply($replyingTo, $submission);
            $this->emitEvent('comment.attach.reply', [$replyingTo, $submission]);
        } else {
            $stream = $this->streamManager->getStream($fields['meerkat_context'], true);
            $stream->attachComment($submission);
        }

        $this->emitEvent('comment.attach.success', $submission);
        event('Form.submission.created', $submission);

        return $this->formSuccess($params, $submission);
    }

    /**
     * Emits an event, impersonating the Statamic Form context.
     *
     * @param $event
     * @param $payload
     * @return array|null
     */
    private function emitCoreFormEvent($event, $payload)
    {
        return event('Form.'.$event, $payload);
    }

    private function runCreatingEvent($submission)
    {
        $errors = [];

        $responses = $this->emitCoreFormEvent('submission.creating', $submission);

        foreach ($responses as $response) {
            // Ignore any non-arrays
            if (! is_array($response)) {
                continue;
            }

            // If the event returned errors, tack those onto the array.
            if ($response_errors = array_get($response, 'errors')) {
                $errors = array_merge($response_errors, $errors);
                continue;
            }

            // If the event returned a submission, we'll replace it with that.
            $submission = array_get($response, 'submission');
        }

        return [$errors, $submission];
    }

    private function formFailure($params, $errors, $formset)
    {
        if (request()->ajax()) {

            return response([
                'errors' => (new MessageBag($errors))->getMessages()
            ], 400);
        }

        // Set up where to be taken in the event of an error.
        if ($error_redirect = array_get($params, 'error_redirect')) {
            $error_redirect = redirect($error_redirect);
        } else {
            $error_redirect = back();
        }

        $jumpSuffix = $this->getJumpSuffix();

        $currentTargetUrl = $error_redirect->getTargetUrl().$jumpSuffix;
        $error_redirect->setTargetUrl($currentTargetUrl);

        return $error_redirect->withInput()->withErrors($errors, 'form.' . $formset);
    }

    /**
     * Returns a formatted anchor that can be embedded into HTML pages.
     *
     * @param  int $id
     * @return string
     */
    private function getJumpSuffix($id = null)
    {
        $jumpValue = '#comments';

        if (request()->has('meerkat_jump')) {
            $jumpValue = request('meerkat_jump');

            if (Str::startsWith($jumpValue, 'to:')) {
                $jumpValue = Str::substr($jumpValue, 3);
            } else if (Str::startsWith($jumpValue, 'comment:id') && $id != null) {
                $jumpValue = '#comment-'.$id;
            } else if (Str::startsWith($jumpValue, 'comment:id|') && $id == null) {
                $jumpValue = Str::substr($jumpValue, 11);
            }
        }

        return $jumpValue;
    }

    /**
     * The steps for a successful form submission.
     *
     * Used for actual success and by honeypot.
     *
     * @param  array $params
     * @param  Forms\Submission $submission
     * @return Response
     */
    private function formSuccess($params, $submission)
    {
        if (request()->ajax()) {
            return response([
                'success' => true,
                'submission' => Factory::makeApiData(MeerkatAPI::findOrFail($submission->id()))
            ]);
        }

        $redirect = array_get($params, 'redirect');

        $response = ($redirect) ? redirect($redirect) : back();
        
        $jumpSuffix = $this->getJumpSuffix($submission->id());

        $currentTargetUrl = $response->getTargetUrl().$jumpSuffix;
        $response->setTargetUrl($currentTargetUrl);

        $this->formFlash->put('form.meerkat.success', true);
        $this->formFlash->put('submission', $submission);

        return $response;
    }

    public function getCounts(Manager $manager)
    {
        if (!$this->accessManager->canViewComments()) {
            if (request()->ajax()) {
                return response('Unauthorized.', 401);
            } else {
                abort(403);
                return;
            }
        }

        $items = $manager->allComments(true);

        $counts = with(new CommentMetrics())->setComments($items)->toArray();

        return compact('counts');
    }

}
