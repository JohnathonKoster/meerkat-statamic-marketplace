<?php

namespace Statamic\Addons\Meerkat\Comments\Spam\Detectors;

use OpenClassrooms\Akismet\Models\Impl\CommentBuilderImpl;
use OpenClassrooms\Akismet\Models\Comment;
use OpenClassrooms\Akismet\Services\Impl\AkismetServiceImpl;
use OpenClassrooms\Akismet\Client\Impl\ApiClientImpl;
use Statamic\Addons\Meerkat\Contracts\Comments\Spam\SpamDetector;
use Statamic\Extend\Extensible;

class AkismetDetector implements SpamDetector
{
    use Extensible;

    /**
     * The AkismetServiceImpl instance.
     *
     * @var AkismetServiceImpl
     *
     */
    protected $akismet;

    protected $errors = [];

    /**
     * The CommentBuilder factory.
     *
     * @var CommentBuilderImpl
     */
    protected $factory;

    protected $success = false;

    protected $errorMessage = '';

    /**
     * Gets the name of the spam detector.
     *
     * @return string
     */
    public function getName()
    {
        return 'Akismet';
    }

    /**
     * Gets a value indicating if the detector succeeded.
     *
     * @return boolean
     */
    public function wasSuccess()
    {
        return $this->success;
    }

    /**
     * Gets an error message string, if available.
     *
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    public function __construct($apiKey, $frontPage)
    {
        $this->addon_name = 'Meerkat';
        $this->akismet = new AkismetServiceImpl;
        $this->akismet->setApiClient(new ApiClientImpl($apiKey, $frontPage));
        $this->factory = new CommentBuilderImpl;
    }

    /**
     * Update Akismet by indicating the data is spam.
     *
     * @param array $data
     *
     * @return void
     */
    public function submitSpam($data)
    {
        try {
            $result = $this->akismet->submitSpam($this->makeComment($data));

            if ($result === "Thanks for making the web a better place.") {
                $this->success = true;
            } else {
                $this->success = false;
                $this->errorMessage = $this->trans('errors.guard_service_error');
            }
        } catch (\Exception $e) {
           $this->errors[] = $e;
           $this->success = false;
           $this->errorMessage = $e->getMessage();
       }
    }

    /**
     * Submits ham to the Akismet service.
     *
     * @param $data
     *
     * @return void
     */
    public function submitHam($data)
    {
        try {
            $result = $this->akismet->submitHam($this->makeComment($data));

            if ($result === "Thanks for making the web a better place.") {
                $this->success = true;
            } else {
                $this->success = false;
                $this->errorMessage = $this->trans('errors.guard_service_error');
            }
        } catch (\Exception $e) {
            $this->errors[] = $e;
            $this->success = false;
            $this->errorMessage = $e->getMessage();
        }
    }

    /**
     * Transforms the Meerkat supplied data into a format expected by the Akismet spam detector.
     *
     * It is possible that a user has changed the form field configuration.
     * If this is the case, the user would have to update their config
     * and tell Meerkat which fields go where in `akismet_fields`.
     *
     * @param  array $data
     * @return array
     */
    private function transform($data)
    {
        $akismetData = [];
        
        foreach ($this->getConfig('akismet_fields') as $akismetKey => $meerkatKey) {
            $akismetData[$akismetKey] = array_get($data, $meerkatKey, '');
        }

        return $akismetData;
    }

    /**
     * Creates a Comment instance from the provided data.
     *
     * @param  array   $data
     * @return Comment
     */
    private function makeComment($data)
    {
        $data = $this->transform($data);

        return $this->factory->create()
            ->withAuthorEmail($data['email'])
            ->withAuthorName($data['author'])
            ->withPermalink($data['permalink'])
            ->withReferrer($data['referrer'])
            ->withUserAgent($data['user_agent'])
            ->withUserIp($data['user_ip'])
            ->withContent($data['content'])
            ->build();
    }

    /**
     * Determines if the given data is spam or not.
     *
     * @param  array $data
     * @return bool
     */
    public function isSpam($data)
    {
        // If there is an Akismet failure, should we automatically mark as spam?
        $spamOnFailure = $this->getConfig('akismet_spam_on_failure', false);

        try {
            return $this->akismet->commentCheck($this->makeComment($data));
        } catch (\Exception $e) {
            $this->errors[] = $e;
            $this->errorMessage = $e->getMessage();
        }

        return $spamOnFailure;
    }




}
