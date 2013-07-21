<?php

namespace PW\PostBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use PW\PostBundle\Document\Post;

class PostEvent extends Event
{
    /**
     * @var \PW\PostBundle\Document\Post
     */
    protected $post;

    /**
     * @param \PW\PostBundle\Document\Post $post
     */
    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    /**
     * @return \PW\PostBundle\Document\Post
     */
    public function getPost()
    {
        return $this->post;
    }
}