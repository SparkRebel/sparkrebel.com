<?php

namespace PW\UserBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpFoundation\Request,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Route,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Method,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Template,
    JMS\SecurityExtraBundle\Annotation\Secure,
    PW\UserBundle\Document\User,
    PW\BoardBundle\Document\Board,
    PW\BoardBundle\Form\Type\CreateAdminCelebBoardType,
    PW\BoardBundle\Form\Model\CelebBoard,
    PW\BoardBundle\Form\Model\AdminCelebBoardType;

/**
 * CelebsController
 */
class CelebsController extends Controller
{

    /**
     * Index action
     *
     * @param Request $request object
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/celebs", name="admin_celebs")
     * @Template
     */
    public function indexAction(Request $request)
    {
        $dm = $this->container->get('doctrine_mongodb.odm.document_manager');
        $user = $dm->getRepository('PWUserBundle:User')->findOneByName('Celebs');


        if (!$user) {
            throw $this->createNotFoundException("Celebs user not found");
        }

       	$qb = $this->getBoardsQb($user, $dm);
		$paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate($qb,
            $request->query->get('page', 1),
            $request->query->get('pagesize', 15)
        );


        return array('celebs' => $pagination);
    }


    /**
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/celebs/new", name="admin_celeb_create")
     * @Template
     */
    public function createAction(Request $request)
    {
        $dm = $this->container->get('doctrine_mongodb.odm.document_manager');
        $user = $dm->getRepository('PWUserBundle:User')->findOneByName('Celebs');

        $boardForm = $this->createForm(
            new CreateAdminCelebBoardType(),
            new CelebBoard()
        );


        if ($request->getMethod() == 'POST') {
            $boardForm->bindRequest($request);
            if ($boardForm->isValid()) {
                $formData = $boardForm->getData();
                $board = $formData->getBoard();
                $board->setCreatedBy($user);
                if ($formData->getNewIcon()) {
                    $asset = $this->get('pw.asset')->addUploadedFile($formData->getNewIcon());
                    $board->setIcon($asset);
                }
                $this->get('pw_board.board_manager')->save($board);
                $this->get('session')->setFlash('success', 'New celeb board has been created');
                return $this->redirect($this->generateUrl('admin_celebs'));
            } else {
                $this->get('session')->setFlash('error', "There was a problem with the information you entered.");
            }
        }

        return array(
            'form'      => $boardForm->createView(),
            'form_path' => $this->generateUrl('admin_celeb_create'),
        );
    }


    /**
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/celebs/edit/{id}", name="admin_celebs_edit")
     * @Template
     */
    public function editAction(Request $request, $id)
    {
        $dm = $this->container->get('doctrine_mongodb.odm.document_manager');
        $user = $dm->getRepository('PWUserBundle:User')->findOneByName('Celebs');


        $board = $this->get('pw_board.board_manager')->find($id);
        if (!$board) {
            throw $this->createNotFoundException("Board not found");
        }

        $boardForm = $this->createForm(
            new CreateAdminCelebBoardType(),
            new CelebBoard($board)
        );


        if ($request->getMethod() == 'POST') {
            $boardForm->bindRequest($request);
            if ($boardForm->isValid()) {
                $formData = $boardForm->getData();
                $board = $formData->getBoard();
                if ($formData->getNewIcon()) {
                    $asset = $this->get('pw.asset')->addUploadedFile($formData->getNewIcon());
                    $board->setIcon($asset);
                }
                $this->get('pw_board.board_manager')->update($board);
                $this->get('session')->setFlash('success', 'Celeb board has been updated');
                return $this->redirect($this->generateUrl('admin_celebs'));
            } else {
                $this->get('session')->setFlash('error', "There was a problem with the information you entered.");
            }
        }

        return array(
            'form'      => $boardForm->createView(),
            'form_path' => $this->generateUrl('admin_celebs_edit', array('id' => $board->getId())),
            'celeb'     => $board
        );
    }

    /**
     * @Method("GET")
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/celeb/delete/{id}", name="admin_celeb_delete")
     * @Template
     */
    public function deleteAction(Request $request, $id)
    {
        $boardManager = $this->get('pw_board.board_manager');
        $celeb = $boardManager->find($id);
        if (!$celeb) {
            throw $this->createNotFoundException("Celeb not found");
        }

        $me = $this->get('security.context')->getToken()->getUser();
        $boardManager->delete($celeb, $me);

        $this->get('session')->setFlash('success', sprintf("Successfully deleted Celeb '%s'", $celeb->getName()));
        return $this->redirect($this->generateUrl('admin_celebs'));
    }

    protected function getBoardsQb(User $user, $dm)
    {
    	$boardRepo = $dm->getRepository('PWBoardBundle:Board');

        return $boardRepo
            ->createQueryBuilder()
            ->field('createdBy')->references($user)
            ->field('isActive')->equals(true)
            ->sort('name', 'asc');

    }


}
