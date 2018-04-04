<?php

namespace Statamic\Addons\Meerkat;

use Statamic\Addons\Meerkat\Comments\Metrics\CommentMetrics;
use Statamic\API\Data;
use Statamic\API\Crypt;
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
use Statamic\Addons\Meerkat\Core\LicenseUpdater;
use Statamic\Addons\Meerkat\Routes\ExportRoutes;
use Statamic\Addons\Meerkat\Routes\ProtectsRoutes;
use Statamic\Addons\Meerkat\Comments\CommentManager;

class MeerkatController extends Controller
{
    use Extensible, APIRoutes, ExportRoutes, ProtectsRoutes;

    protected $streamManager;

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
        'postUpdatelicense',
        'getExport',
        'getCounts'
    ];

    public function __construct(Manager $streamManager)
    {
        $this->streamManager = $streamManager;
        $this->protectRoutes();
    }

    /**
     * Maps to your route definition in routes.yaml
     *
     * @return Illuminate\Http\Response
     */
    public function index()
    {
        if (!$this->isLicensed()) {
            return $this->view('license', [
                'title' => meerkat_trans('settings.license'),
                'license_key' => $this->getConfig('license_key'),
                'submit' => $this->actionUrl('updatelicense')
            ]);
        }

        return $this->view('streams.index', [
            'title' => meerkat_trans('comments.comments'),
            'form' => MeerkatAPI::getForm(),
            'filter' => Input::get('filter', 'all'),
            'hideManagement' => false
        ]);
    }

    public function postUpdatelicense()
    {
        $licenseKey = request('license_key', '');
        $updater = app(LicenseUpdater::class);

        if ($updater->updateLicense($licenseKey)) {
            return redirect()->to(CP_ROUTE);
        }

        return redirect()->to($this->actionUrl('updateLicense'));
    }

    /**
     * Deletes the specified comments.
     *
     * @param  CommentManager $manager
     * @return array
     */
    public function deleteComments(CommentManager $manager)
    {
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
        $comments = Helper::ensureArray(Input::get('ids', []));

        $errorMessage = null;

        try {
            $manager->markCommentsAsSpam($comments);
            $markSucceeded = true;
        } catch (\Exception $e) {
            $markSucceeded = false;
            $errorMessage = $e->getMessage();
        }

        return [
            'success' => $markSucceeded,
            'marked' => $comments,
            'errorMessage' => $errorMessage
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
        $comments = Helper::ensureArray(Input::get('ids', []));

        $errorMessage = null;

        try {
            $manager->markCommentsAsNotSpam($comments);
            $markSucceeded = true;
        } catch (\Exception $e) {
            $markSucceeded = false;
            $errorMessage = $e->getMessage();
        }

        return [
            'success' => $markSucceeded,
            'marked' => $comments,
            'errorMessage' => $errorMessage
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

    /**
     * Updates the specified comments.
     *
     * @param  CommentManager $manager
     * @return array
     */
    public function updateComment(CommentManager $manager)
    {
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
            'errorMessage' => $errorMessage
        ];
    }

    public function getComments($contextStream)
    {
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

        $this->flash->put('success', true);
        $this->flash->put('submission', $comment);

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

        if (isset($fields['ids']) && count($fields['ids']) > 0) {            
            if (is_array($fields['ids'])) {
                $replyingTo = $fields['ids'][0];
            } else {
                $replyingTo = $fields['ids'];
            }

            $isReply = true;
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

        if ($isReply) {
            $stream = $this->streamManager->getEmptyStream();
            $stream->attachReply($replyingTo, $submission);
            $this->emitEvent('comment.attach.reply', [$replyingTo, $submission]);
        } else {
            $stream = $this->streamManager->getStream($fields['meerkat_context'], true);
            $stream->attachComment($submission);
        }

        $this->emitEvent('comment.attach.success', $submission);

        return $this->formSuccess($params, $submission);
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

        return $error_redirect->withInput()->withErrors($errors, 'form.' . $formset);
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

        $this->flash->put('success', true);
        $this->flash->put('submission', $submission);

        return $response;
    }

    public function getCounts(Manager $manager)
    {
        $items = $manager->allComments();
        $counts = with(new CommentMetrics())->setComments($items)->toArray();

        return compact('counts');
    }

}
