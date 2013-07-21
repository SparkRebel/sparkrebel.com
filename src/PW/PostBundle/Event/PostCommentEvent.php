<?php

namespace PW\PostBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use PW\PostBundle\Document\PostComment;

class PostCommentEvent extends Event
{
    /**
     * @var \PW\PostBundle\Document\PostComment
     */
    protected $postComment;

    /**
     * @param \PW\PostBundle\Document\PostComment $postComment
     */
    public function __construct(PostComment $postComment)
    {
        $this->postComment = $postComment;
    }

    /**
     * @return \PW\PostBundle\Document\PostComment
     */
    public function getPostComment()
    {
        return $this->postComment;
    }
}