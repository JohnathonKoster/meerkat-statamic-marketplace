<?php

namespace Statamic\Addons\Meerkat;

use Statamic\API\File;
use Statamic\API\YAML;
use Statamic\Extend\API;
use Statamic\Addons\Meerkat\Forms\Form;
use Statamic\Addons\Meerkat\Comments\Stream;
use Statamic\Addons\Meerkat\Comments\CommentManager;
use Statamic\Addons\Meerkat\Comments\CommentCollection;
use Statamic\Addons\Meerkat\Comments\CommentNotFoundException;

class MeerkatAPI extends API
{

    /**
     * Gets the Meerkat version number.
     *
     * @return string
     */
    public static function version()
    {
        return YAML::parse(file_get_contents(__DIR__.'/meta.yaml'))['version'];
    }

    /**
     * Gets the Meerkat submission form.
     *
     * @return Form
     */
    public static function getForm()
    {
        $formset = app('Statamic\Contracts\Forms\Formset');
        $formset->name(MeerkatTags::MEERKAT_FORMSET);

        $form = new Form;
        $form->name(MeerkatTags::MEERKAT_FORMSET);
        $form->formset($formset);

        $formset = $form->formset();
        $formset->data(YAML::parse(File::get(settings_path('formsets/'.MeerkatTags::MEERKAT_FORMSET.'.yaml'))));

        $form->formset($formset);

        return $form;
    }

    /**
     * This method is referenced as an example in the docs ;)
     *
     * @throws \Exception
     */
    public static function someMethod()
    {
        throw new \Exception('Hey there! Seems like your reading the docs! Good job; keep reading to learn about the API.');
    }

    /**
     * Gets the comments for the given context.
     *
     * @param  $context
     * @return mixed
     */
    public static function getComments($context)
    {
        return with(new Stream($context))->getComments();
    }

    /**
     * Finds the given comment or throws an exceptions.
     *
     * @param  $commentID
     * @return mixed
     *
     * @throws CommentNotFoundException
     */
    public static function findOrFail($commentID)
    {
        return app(CommentManager::class)->findOrFail($commentID);
    }

    /**
     * Gets the comment (or comments) from the file store.
     *
     * If you supply an array for $commentID, multiple comments
     * will be returned from this method. This method always
     * return an iterable collection, regardless of number.
     *
     * @param  string|array $comment
     * @return CommentCollection
     */
    public static function findComments($comment)
    {
        return app(CommentManager::class)->getComments($comment);
    }

    /**
     * Get's the context for a given comment ID.
     *
     * @return Statamic\Data\Entries\Entry
     *
     * @throws CommentNotFoundException
     */
    public static function getContext($commentID)
    {
        $comment = self::findOrFail($commentID);

        return $comment->get('context');
    }

    /**
     * Get's the context for a given comment ID.
     *
     * @return Statamic\Data\Entries\Entry
     *
     * @throws CommentNotFoundException
     */
    public static function getEntry($comment)
    {
        return self::getContext($commentID);
    }

}
