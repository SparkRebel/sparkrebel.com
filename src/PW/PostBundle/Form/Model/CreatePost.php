<?php

namespace PW\PostBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert,
    PW\PostBundle\Document\Post;

class CreatePost
{
    /**
     * @Assert\Type(type="PW\PostBundle\Document\Post")
     * @Assert\Valid
     */
    protected $post;

    /**
     * @var bool
     */
    protected $postOnFacebook = false;

    /**
     * @param Post $post
     */
    public function __construct(Post $post = null, $postOnFacebook = false)
    {
        $this->post = $post;
        $this->setPostOnFacebook($postOnFacebook);
    }

    /**
     * @param Post $post
     */
    public function setPost(Post $post)
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

    /**
     * @param bool $postOnFacebook
     */
    public function setPostOnFacebook($postOnFacebook)
    {
        $this->postOnFacebook = $postOnFacebook;
    }

    /**
     * @return bool
     */
    public function getPostOnFacebook()
    {
        return (bool) $this->postOnFacebook;
    }
}
