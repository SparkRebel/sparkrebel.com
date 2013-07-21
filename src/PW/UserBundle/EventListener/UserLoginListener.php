<?php

namespace PW\UserBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerAware,
    Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class UserLoginListener extends ContainerAware
{
    /**
     * @param InteractiveLoginEvent $event
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        /* @var $userManager \PW\UserBundle\Model\UserManager */
        $userManager = $this->container->get('pw_user.User_manager');

        $user = $event->getAuthenticationToken()->getUser();

        if ($user instanceof \PW\UserBundle\Document\User) {
            $user->incLoginCount();

            /* @var $userCounts \PW\UserBundle\Document\User\Counts */
            $userCounts = $user->getCounts();
            if ($userCounts->isEmpty()) {
                $userManager->processCounts($user);
            }

            if ($user->getUserType() == 'user') {
                // Make sure user has default board
                $this->_verifyDefaultBoards($user);
            }
        }
    }

    /**
     * @param \PW\UserBundle\Document\User $user
     */
    protected function _verifyDefaultBoards($user)
    {
        /* @var $boardManager \PW\BoardBundle\Model\BoardManager */
        $boardManager = $this->container->get('pw_board.board_manager');

        /* @var $categoryManager \PW\CategoryBundle\Model\CategoryManager */
        $categoryManager = $this->container->get('pw_category.category_manager');

        foreach ($boardManager->getDefaultBoards() as $name => $category) {
            $board = $boardManager->getRepository()
                ->findByUser($user, array('includeDeleted' => true))
                ->field('name')->equals($name)
                ->getQuery()->getSingleResult();

            if (!$board) {
                $category = $categoryManager->getRepository()
                    ->findOneBy(array('name' => $category, 'type' => 'user'));

                $board = $boardManager->create(array(
                    'createdBy' => $user,
                    'name'      => $name,
                    'category'  => $category,
                ));
                $boardManager->update($board, true);
            }
        }
    }
}
