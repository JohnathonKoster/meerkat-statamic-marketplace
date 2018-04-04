<?php

namespace Statamic\Addons\Meerkat\DesignerMode;

use Statamic\Addons\Meerkat\Comments\Comment;
use Statamic\Addons\Meerkat\DesignerMode\LoremIpsum;
use Statamic\Addons\Meerkat\Comments\CommentCollection;

class Factory
{

    /**
     * The Lorem Ipsum generator.
     *
     * @var LoremIpsum
     */
    private $ipsum;

    public function __construct()
    {
        $this->ipsum = new LoremIpsum;
    }

    /**
     * Random names generated with http://www.fakenamegenerator.com/
     *
     * @var array
     */
    private $names = [
        'Susan N. Shives',
        'Ellsworth A. Clark',
        'Keith C. Abell',
        'John C. Carlton',
        'Robert N. Delaney',
        'Randal A. Garrett',
        'Mary D. Newman',
        'Jonathon B. Louie',
        'Jeff L. Henson',
        'Nathan L. Winfrey',
        'Beatrice A. Bird',
        'Adela G. Wharton',
        'Nancy J. Baker',
        'Cruz P. Jones',
        'Louise P. Hayes',
        'Charles J. Woods',
        '\'Aliyy Bashir Morcos',
        '\Id Awwab Kassis',
        'Timothy M. Dubose',
        'Sandra S. Christie',
        'Jose S. Guffey'
    ];

    
    /**
     * A collection of avatar URLs. Taken from https://randomuser.me/photos
     *
     * @var array
     */
    private $portraits = [
        'https://randomuser.me/api/portraits/women/27.jpg',
        'https://randomuser.me/api/portraits/women/10.jpg',
        'https://randomuser.me/api/portraits/women/0.jpg',
        'https://randomuser.me/api/portraits/women/94.jpg',
        'https://randomuser.me/api/portraits/women/86.jpg',
        'https://randomuser.me/api/portraits/women/88.jpg',
        'https://randomuser.me/api/portraits/women/53.jpg',
        'https://randomuser.me/api/portraits/women/8.jpg',
        'https://randomuser.me/api/portraits/women/70.jpg',
        'https://randomuser.me/api/portraits/women/41.jpg',
        'https://randomuser.me/api/portraits/women/17.jpg',
        'https://randomuser.me/api/portraits/men/41.jpg',
        'https://randomuser.me/api/portraits/men/91.jpg',
        'https://randomuser.me/api/portraits/men/94.jpg',
        'https://randomuser.me/api/portraits/men/90.jpg',
        'https://randomuser.me/api/portraits/men/56.jpg',
        'https://randomuser.me/api/portraits/men/55.jpg',
        'https://randomuser.me/api/portraits/men/69.jpg',
        'https://randomuser.me/api/portraits/men/64.jpg',
        'https://randomuser.me/api/portraits/men/73.jpg',
        'https://randomuser.me/api/portraits/men/11.jpg',
        'https://randomuser.me/api/portraits/men/71.jpg',
        'https://randomuser.me/api/portraits/men/3.jpg',
        'https://randomuser.me/api/portraits/men/0.jpg',
        'https://randomuser.me/api/portraits/men/63.jpg'        
    ];

    public function getName()
    {
        return $this->names[array_rand($this->names)];
    }

    public function getAvatar()
    {
        return $this->portraits[array_rand($this->portraits)];
    }

    private $randomParticipants = [];

    private function setRandomData(CommentCollection $comments)
    {
        $comments->each(function (Comment $comment) {
            $randomName  = $this->getName();
            $randomEmail = md5(time()).'@example.org';

            $comment->set('name', $randomName);
            $comment->set('email', $randomEmail);
            $comment->set('comment', markdown($this->ipsum->sentences(2)));
            $comment->set('designer_avatar', $this->getAvatar());

            // Update the time.
            $comment->id(time() - rand(1, 60));

            $this->randomParticipants[$randomName.$randomEmail] = [
                'name'  => $randomName,
                'email' => $randomEmail,
                'url'   => '' 
            ];

            if ($comment->hasReplies()) {
                $processedReplies = $this->setRandomData($comment->getReplies());
                $comment->setReplies($processedReplies);
            }
        });

        return $comments;
    }

    private function setConversationParticipants(CommentCollection $comments)
    {
        $comments->each(function (Comment $comment) {
            $comment->setConversationParticipants($this->randomParticipants);

            if ($comment->hasReplies()) {
                $processedReplies = $this->setConversationParticipants($comment->getReplies());
                $comment->setReplies($processedReplies);
            }
        });

        return $comments;
    }

    public function processCollection(CommentCollection $comments)
    {
        $newComments = $this->setRandomData($comments);
        $newComments = $this->setConversationParticipants($newComments);

        return $newComments;
    }

}