<?php

namespace PW\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\SecurityContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use JMS\SecurityExtraBundle\Annotation\Secure;

class DashboardController extends Controller
{
    /**
     * @Method("GET")
     * @Secure(roles="ROLE_USER")
     * @Route("/admin")
     * @Route("/admin/dashboard", name="admin_dashboard_index")
     * @Template
     */
    public function indexAction()
    {
        $security = $this->get('security.context');
        if (!$security->isGranted('ROLE_PREVIOUS_ADMIN') && !$security->isGranted('ROLE_ADMIN')) {
            return new NotFoundHttpException('Not Found', new AccessDeniedHttpException());
        }

        $total_users  = $this->get('pw_user.user_manager')->getRepository()->countTotalActiveUsers();
        $total_boards = $this->get('pw_board.board_manager')->getRepository()->countTotalActiveBoards();
        $total_sparks = $this->get('pw_post.post_manager')->getRepository()->countTotalActivePosts();
        $curator_posts_to_be_processed = $this->get('pw_post.post_manager')->getRepository()->countCuratorSparksToByProcessed();

        return compact('total_users', 'total_sparks', 'total_boards', 'curator_posts_to_be_processed');
    }
}
