<?php

namespace PW\CategoryBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\Security\Core\SecurityContext,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Route,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Template,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Method,
    JMS\SecurityExtraBundle\Annotation\Secure,
    PW\UserBundle\Document\User;


use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
/**
 * SignUpController
 *
 */
class SignUpController extends Controller
{

    /**
     * @Method("GET")
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/signup/areas", name="admin_signup_areas")
     * @Template
     */
    public function areasAction(Request $request)
    {
        $dm = $this->get('doctrine_mongodb.odm.document_manager');
        $qb = $dm->createQueryBuilder('PWCategoryBundle:Area')
            ->field('isActive')->equals(true)
            ->sort('name', 'asc');

        /* @var $paginator \Knp\Component\Pager\Paginator */
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate($qb,
            $request->query->get('page', 1),
            $request->query->get('pagesize', 15)
        );

        return array(
            'areas' => $pagination,
        );
    }


    /**
     * @Method("GET")
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/signup/categories", name="admin_signup_categories")
     * @Template
     */
    public function categoriesAction(Request $request)
    {
        $dm = $this->get('doctrine_mongodb.odm.document_manager');

        $categories = $dm->createQueryBuilder('PWCategoryBundle:Category')
            ->field('isActive')->equals(true)
            ->field('type')->equals('user')
            ->sort('name', 'asc')
            ->getQuery()
            ->execute();

        $boards = array();

        foreach ($categories as $category) {
            $boards[$category->getName()] = $dm->createQueryBuilder('PWBoardBundle:Board')
                ->field('category')->references($category)
                ->field('adminScore')->gt(0)
                ->getQuery()
                ->execute();
        }

        return array(
            'data' => $boards
        );
    }

    
    /**
     * @Method("POST")
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/signup/categories/add", name="admin_signup_category_add")
     */
    public function boardCategoryAddAction(Request $request)
    {
        $dm = $this->get('doctrine_mongodb.odm.document_manager');
        $board_id = $request->request->get('board_id');
        $board = $dm->getRepository('PWBoardBundle:Board')->find($board_id);

        if (!$board) {
            throw new NotFoundHttpException('Board not found');
        }

        $board->setAdminScore(1);
        $dm->persist($board);
        $dm->flush();
        $this->get('session')->setFlash('success', 'Board "'.$board->getName().'" score set to 1');

        return $this->redirect(
            $this->generateUrl('admin_signup_categories')
        );
    }

    /**
     * @Method("DELETE")
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/signup/categories/delete/{id}", name="admin_signup_category_delete")
     */
    public function boardCategoryDeleteAction(Request $request, $id)
    {
        $dm = $this->get('doctrine_mongodb.odm.document_manager');
        $board = $dm->getRepository('PWBoardBundle:Board')->find($id);

        if (!$board) {
            throw new NotFoundHttpException('Board not found');
        }

        $board->setAdminScore(0);
        $dm->persist($board);
        $dm->flush();
        $this->get('session')->setFlash('success', 'Board removed');

        return $this->redirect(
            $this->generateUrl('admin_signup_categories')
        );
    }

    /**
     * @Method("POST")
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/signup/areas/add", name="admin_signup_area_add")
     */
    public function boardAreaAddAction(Request $request)
    {
        $dm = $this->get('doctrine_mongodb.odm.document_manager');
        $board_id = $request->request->get('board_id');
        $area_id = $request->request->get('area_id');
        
        $board = $dm->getRepository('PWBoardBundle:Board')->find($board_id);
        if (!$board) {
            throw new NotFoundHttpException('Board not found');
        }
        $area = $dm->getRepository('PWCategoryBundle:Area')->find($area_id);
        if (!$area) {
            throw new NotFoundHttpException('Area not found');
        }

        $area->addBoards($board);
        $dm->persist($area);
        $dm->flush();
        $this->get('session')->setFlash('success', 'Board added');

        return $this->redirect(
            $this->generateUrl('admin_signup_areas')
        );
    }
    
    
    /**
     * @Method("DELETE")
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/signup/areas/delete/{id}/{area_id}", name="admin_signup_area_delete")
     */
    public function boardAreaDeleteAction(Request $request, $id, $area_id)
    {
        $dm = $this->get('doctrine_mongodb.odm.document_manager');
        $board = $dm->getRepository('PWBoardBundle:Board')->find($id);

        if (!$board) {
            throw new NotFoundHttpException('Board not found');
        }
        $area = $dm->getRepository('PWCategoryBundle:Area')->find($area_id);
        if (!$area) {
            throw new NotFoundHttpException('Area not found');
        }

        $area->removeBoard($board);
        $dm->persist($area);
        $dm->flush();
        $this->get('session')->setFlash('success', 'Board removed');

        return $this->redirect(
            $this->generateUrl('admin_signup_areas')
        );
    }



}
