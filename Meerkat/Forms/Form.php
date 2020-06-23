<?php

namespace Statamic\Addons\Meerkat\Forms;

use Statamic\Forms\Form as StatamicForm;
use Statamic\Addons\Meerkat\Comments\Comment;
use Statamic\Addons\Meerkat\Comments\Manager;
use Statamic\Addons\Meerkat\Comments\Stream;

class Form extends StatamicForm
{

    /**
     * Indicates if we should limit submissions, and if so to what.
     *
     * @var null|string
     */
    protected $limitSubmissionsToContext = null;

    /**
     * Gets a submission
     *
     * @return Submission
     */
    public function createSubmission()
    {
        /** @var Submission $submission */
        $submission = app(Submission::class);

        $submission->form($this);

        return $submission;
    }

    /**
     * Sets whether or not we should be limiting submissions.
     *
     * @param $limitTo
     */
    public function limitSubmissions($limitTo)
    {
        $this->limitSubmissionsToContext = $limitTo;
    }

    protected function collectComments($value = [])
    {
        return new \Statamic\Addons\Meerkat\Comments\CommentCollection($value);
    }

    public function submissions()
    {
        $submissions = $this->collectComments();

        /** @var Manager $manager */
        $manager = app(Manager::class);

        if ($this->limitSubmissionsToContext == null) {
            return $manager->allComments(true);
        } else {
            $submissions = $manager->getStream($this->limitSubmissionsToContext)->getComments();
        }

        return $submissions;
    }


}