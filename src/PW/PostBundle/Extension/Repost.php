<?php

namespace PW\PostBundle\Extension;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use     PW\PostBundle\Document\Post,
    PW\PostBundle\Form\Type\CreatePostType,
    PW\PostBundle\Form\Type\CreateRepostType,
    PW\PostBundle\Form\Model\CreatePost,
    PW\PostBundle\Form\Model\CreateRepost,
    PW\BoardBundle\Document\Board,
    PW\BoardBundle\Form\Type\CreateBoardType,
    PW\BoardBundle\Form\Type\CreateRepostBoardType,
    PW\BoardBundle\Form\Model\CreateBoard;
class Repost extends \Twig_Extension implements ContainerAwareInterface
{

    /**
     * @var ContainerInterface
     *
     * @api
     */
    protected $container;

    /**
     * Sets the Container associated with this Controller.
     *
     * @param ContainerInterface $container A ContainerInterface instance
     *
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function getFunctions()
    {
        return array(
            'get_repost_form'  => new \Twig_Function_Method($this, 'getRepostForm'),
            'get_board_repost_form'  => new \Twig_Function_Method($this, 'getBoardRepostForm'),
            'get_boards'  => new \Twig_Function_Method($this, 'getBoards'),
        );
    }


    public function getRepostForm()
    {
        $me = $this->getMe();
        $userSettings = $me->getSettings();
        $postOnFacebook = true;
        $session = $this->container->get('session');
        if ($session->get('unchecked_postOnFacebook',false)) {
            $postOnFacebook = !empty($userSettings['postOnFacebook']);
        }
        $postForm = $this->container->get('form.factory')->create(
            new CreateRepostType($me, $postOnFacebook),
            new CreatePost(new Post, $postOnFacebook)
        );   
        return $postForm->createView();
    }

    public function getBoardRepostForm()
    {        
        $me = $this->getMe();
        $boardForm = $this->container->get('form.factory')->create(
            new CreateBoardType(),
            new CreateBoard()
        );
        return $boardForm->createView();
    }

    public function getBoards()
    {
        return $this->getMe()->getBoards();
    }


    /**
     * getName
     *
     * @return string
     */
    public function getName()
    {
        return 'pw_repost';
    }

    public function getMe()
    {
        return $this->container->get('security.context')->getToken()->getUser();
    }
}
