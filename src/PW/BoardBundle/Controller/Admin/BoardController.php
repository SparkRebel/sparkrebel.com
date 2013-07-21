<?php

namespace PW\BoardBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\Security\Core\SecurityContext,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Route,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Template,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Method,
    JMS\SecurityExtraBundle\Annotation\Secure,
    PW\UserBundle\Document\User,
    PW\BoardBundle\Document\Board,
    PW\BoardBundle\Form\Type\AdminChangeBoardOwnerType;

class BoardController extends Controller
{
    /**
     * @Method("GET")
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/boards/{status}", defaults={"status"="all"}, name="admin_board_index")
     * @Template
     */
    public function indexAction(Request $request, $status)
    {
        /* @var $boardManager \PW\BoardBundle\Model\BoardManager */
        $boardManager = $this->get('pw_board.board_manager');
        $qb = $boardManager->getRepository()->findByStatus($status);

        /* @var $paginator \Knp\Component\Boardr\Paginator */
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate($qb,
            $request->query->get('page', 1),
            $request->query->get('pagesize', 15)
        );

        return array(
            'boards' => $pagination,
        );
    }

    /**
     * @Method("GET")
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/board/delete/{id}", name="admin_board_delete")
     * @Template
     */
    public function deleteAction(Request $request, $id)
    {
        /* @var $boardManager \PW\BoardBundle\Model\BoardManager */
        $boardManager = $this->get('pw_board.board_manager');
        $board = $boardManager->find($id);
        if (!$board) {
            throw $this->createNotFoundException("Board not found");
        }

        $me = $this->get('security.context')->getToken()->getUser();
        $boardManager->delete($board, $me);

        $this->get('session')->setFlash('success', sprintf("Successfully deleted Board '%s'", $board->getName()));
        return $this->redirect($this->generateUrl('admin_board_index', array('status' => 'deleted')));
    }

    /**
     * @Method("GET")
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/board/undelete/{id}", name="admin_board_undelete")
     * @Template
     */
    public function unDeleteAction(Request $request, $id)
    {
        /* @var $boardManager \PW\BoardBundle\Model\BoardManager */
        $boardManager = $this->get('pw_board.board_manager');
        $board = $boardManager->find($id);
        if (!$board) {
            throw $this->createNotFoundException("Board not found");
        }

        $me = $this->get('security.context')->getToken()->getUser();
        $boardManager->unDelete($board);

        $this->get('session')->setFlash('success', sprintf("Successfully undeleted Board '%s'", $board->getName()));
        return $this->redirect($this->generateUrl('admin_board_index'));
    }

    /**
     * @Method("POST")
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/board/undeleteposts/{id}", name="admin_board_undeleteposts")
     * @Template
     */
    public function unDeletePostsAction(Request $request, $id)
    {
        /* @var $boardManager \PW\BoardBundle\Model\BoardManager */
        $boardManager = $this->get('pw_board.board_manager');
        $board = $boardManager->find($id);
        if (!$board) {
            throw $this->createNotFoundException("Board not found");
        }

        $me = $this->get('security.context')->getToken()->getUser();

        $qb = $this->get('pw_post.post_manager')->getRepository()->findBoardPostsIncludingDeleted($board);

        $posts = $qb
                    ->field('isActive')->equals(false)
                    ->getQuery()
                    ->execute();        
        $total = 0;
        foreach ($posts as $post) {
            $this->get('pw_post.post_manager')->unDelete($post, false);    
            $total++;
        }
        $this->get('pw_post.post_manager')->flush();
        

        $this->get('session')->setFlash('success', sprintf("Successfully undeleted %d posts for Board '%s'", $total, $board->getName()));
        return $this->redirect($this->generateUrl('admin_board_index'));
    }

    
    
    
    /**
     * @Method("GET")
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/board/syncCount/{id}", name="admin_board_synccount")
     * @Template
     */
    public function syncCountAction(Request $request, $id)
    {
        /* @var $boardManager \PW\BoardBundle\Model\BoardManager */
        $boardManager = $this->get('pw_board.board_manager');
        $board = $boardManager->find($id);
        if (!$board) {
            throw $this->createNotFoundException("Board not found");
        }

        $me = $this->get('security.context')->getToken()->getUser();
        $boardManager->processCounts($board);

        $this->get('session')->setFlash('success', sprintf("Successfully synced Board '%s'", $board->getName()));
        return $this->redirect($this->generateUrl('admin_board_index'));
    }
    
    
    /**
     * @Method("GET")
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/intern-boards", name="admin_intern_boards")
     * @Template
     */
    public function internBoardsAction(Request $request)
    {
        $userManager = $this->get('pw_user.user_manager');
        $interns = $userManager->getRepository()->findInternsAdminsAndCuratorsWithBoards();

        return array(
            'interns' => $interns,
        );
    }
    
    
    /**
     * @Method({"GET", "POST"})
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/intern-boards/{id}/change-owner", name="admin_change_board_owner")
     * @Template
     */
    public function changeOwnerAction(Request $request, $id)
    {
        /* @var $boardManager \PW\BoardBundle\Model\BoardManager */
        $boardManager = $this->get('pw_board.board_manager');
        $board = $boardManager->find($id);
        if (!$board) {
            throw $this->createNotFoundException("Board not found");
        }
                        
        $form =  $this->createForm(new AdminChangeBoardOwnerType($board));
        
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);
            if ($form->isValid()) {
                $formData = $form->getData();
                $new_owner = $formData->getCreatedBy();                
                $boardManager->changeOwner($board, $new_owner);
                $this->get('session')->setFlash('success', 'Owner of board have been changed successfully');
                return $this->redirect($this->generateUrl('admin_intern_boards'));
            }
            $this->get('session')->setFlash('error', 'Changing owner of board failed');
        }
        
        return array(
            'board' => $board,
            'form'  => $form->createView()
        );
     
    }
    
    
    
}
