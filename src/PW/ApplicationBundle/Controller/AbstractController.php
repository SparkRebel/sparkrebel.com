<?php

namespace PW\ApplicationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;

abstract class AbstractController extends Controller
{
    protected $dm;
    protected $userManager;
    protected $followManager;
    protected $boardManager;
    protected $postManager;
    protected $eventManager;
    protected $assetManager;

    /**
     * @return \PW\UserBundle\Document\User
     */
    protected function getCurrentUser()
    {
        return $this->container->get('security.context')->getToken()->getUser();
    }

    /**
     * @return \Doctrine\ODM\MongoDB\DocumentManager
     */
    protected function getDocumentManager()
    {
        if ($this->dm == null) {
            $this->dm = $this->container->get('doctrine_mongodb.odm.document_manager');
        }

        return $this->dm;
    }

    /**
     * @return \PW\UserBundle\Model\UserManager
     */
    protected function getUserManager()
    {
        if ($this->userManager == null) {
            $this->userManager = $this->container->get('pw_user.user_manager');
        }

        return $this->userManager;
    }

    /**
     * @return \PW\UserBundle\Model\FollowManager
     */
    protected function getFollowManager()
    {
        if ($this->followManager == null) {
            $this->followManager = $this->container->get('pw_user.follow_manager');
        }

        return $this->followManager;
    }

    /**
     * @return \PW\BoardBundle\Model\BoardManager
     */
    protected function getBoardManager()
    {
        if ($this->boardManager == null) {
            $this->boardManager = $this->container->get('pw_board.board_manager');
        }

        return $this->boardManager;
    }

    /**
     * @return \PW\PostBundle\Model\PostManager
     */
    protected function getPostManager()
    {
        if ($this->postManager == null) {
            $this->postManager = $this->container->get('pw_post.post_manager');
        }

        return $this->postManager;
    }

    /**
     * @return \PW\ApplicationBundle\Model\EventManager
     */
    protected function getEventManager()
    {
        if ($this->eventManager == null) {
            $this->eventManager = $this->container->get('pw.event');
        }

        return $this->eventManager;
    }

    /**
     * @return <type>
     */
    protected function getAssetManager()
    {
        if ($this->assetManager == null) {
            $this->assetManager = $this->container->get('pw.asset');
        }

        return $this->assetManager;
    }

    /**
     * @param Form $form
     * @return string
     */
    protected function _getFirstErrorMessage(Form $form)
    {
        $errors = $this->_getErrorMessages($form);
        return $this->_getFirstMessage($errors);
    }

    /**
     * @param Form $form
     * @return array
     */
    protected function _getErrorMessages(Form $form)
    {
        $errors = array();
        foreach ($form->getErrors() as $key => $error) {
            $errors[] = strtr($error->getMessageTemplate(), $error->getMessageParameters());
        }
        if ($form->hasChildren()) {
            foreach ($form->getChildren() as $child) {
                if (!$child->isValid()) {
                    $errors[$child->getName()] = $this->_getErrorMessages($child);
                }
            }
        }
        return $errors;
    }

    /**
     * @param type $errors
     * @return string|bool
     */
    private function _getFirstMessage($errors)
    {
        if (is_string($errors)) {
            return $errors;
        }
        if (is_array($errors)) {
            foreach ($errors as $key => $value) {
                return $this->_getFirstMessage($value);
            }
        }
        return false;
    }
}
