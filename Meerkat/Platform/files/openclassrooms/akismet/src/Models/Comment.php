<?php

namespace OpenClassrooms\Akismet\Models;

/**
 * @author Arnaud Lefèvre <arnaud.lefevre@openclassrooms.com>
 */
abstract class Comment
{

    /**
     * IP address of the comment submitter.
     *
     * @var string
     */
    protected $userIp;

    /**
     * User agent string of the web browser submitting the comment - typically the HTTP_USER_AGENT cgi variable.
     * Not to be confused with the user agent of your Akismet library.
     *
     * @var string
     */
    protected $userAgent;

    /**
     * The content of the HTTP_REFERER header should be sent here.
     *
     * @var string
     */
    protected $referrer;

    /**
     * The permanent location of the entry the comment was submitted to.
     *
     * @var string
     */
    protected $permalink;

    /**
     * Name submitted with the comment.
     *
     * @var string
     */
    protected $authorName;

    /**
     * Email address submitted with the comment.
     *
     * @var string
     */
    protected $authorEmail;

    /**
     * The content that was submitted.
     * Please send the raw text the commenter has entered.
     * HTML and forum tags are accepted.
     *
     * @var string
     */
    protected $content;

    /**
     * @return string
     */
    public function getAuthorEmail()
    {
        return $this->authorEmail;
    }

    /**
     * @param string $authorEmail
     */
    public function setAuthorEmail($authorEmail)
    {
        $this->authorEmail = $authorEmail;
    }

    /**
     * @return string
     */
    public function getAuthorName()
    {
        return $this->authorName;
    }

    /**
     * @param string $authorName
     */
    public function setAuthorName($authorName)
    {
        $this->authorName = $authorName;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getPermalink()
    {
        return $this->permalink;
    }

    /**
     * @param string $permalink
     */
    public function setPermalink($permalink)
    {
        $this->permalink = $permalink;
    }

    /**
     * @return string
     */
    public function getReferrer()
    {
        return $this->referrer;
    }

    /**
     * @param string $referrer
     */
    public function setReferrer($referrer)
    {
        $this->referrer = $referrer;
    }

    /**
     * @return string
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }

    /**
     * @param string $userAgent
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;
    }

    /**
     * @return string
     */
    public function getUserIp()
    {
        return $this->userIp;
    }

    /**
     * @param string $userIp
     */
    public function setUserIp($userIp)
    {
        $this->userIp = $userIp;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array(
            'user_ip'              => $this->getUserIp(),
            'user_agent'           => $this->getUserAgent(),
            'referrer'             => $this->getReferrer(),
            'permalink'            => $this->getPermalink(),
            'comment_author'       => $this->getAuthorName(),
            'comment_author_email' => $this->getAuthorEmail(),
            'comment_content'      => $this->getContent()
        );
    }
}
