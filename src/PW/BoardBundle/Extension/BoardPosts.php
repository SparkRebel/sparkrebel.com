<?php

namespace PW\BoardBundle\Extension;

use PW\BoardBundle\Document\Board,
    PW\PostBundle\Model\PostManager;

class BoardPosts extends \Twig_Extension
{
    /**
     * @var \PW\PostBundle\Model\PostManager
     */
    protected $postManager;

    public function getFilters()
    {
        return array(
            'posts' => new \Twig_Filter_Method($this, 'boardPosts'),
        );
    }

    /**
     * TODO Ludicrous
     *
     * @param Board $board
     * @param int $limit
     * @param int $skip
     * @return collection Posts
     */
    public function boardPosts(Board $board = null, $limit = 0, $skip = 0)
    {
        if (empty($board)) {
            return null;
        }

        return $this->postManager->getRepository()
            ->findByBoard($board)
            ->limit($limit)->skip($skip)
            ->getQuery()->execute();
    }

    public function getName()
    {
        return 'pw_board_posts';
    }

    public function setPostManager(PostManager $postManager = null)
    {
        $this->postManager = $postManager;
    }
}
