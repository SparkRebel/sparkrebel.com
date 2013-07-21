<?php

namespace PW\CmsBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\Security\Core\SecurityContext,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Route,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Template,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Method,
    JMS\SecurityExtraBundle\Annotation\Secure,
    PW\UserBundle\Document\User,
    PW\CmsBundle\Document\Page,
    PW\CmsBundle\Form\Model\CreatePage,
    PW\CmsBundle\Form\Type\CreatePageType;

class PageController extends Controller
{
    /**
     * @Method("GET")
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/cms/pages/{status}", defaults={"status"="all"}, name="admin_cms_page_index")
     * @Template
     */
    public function indexAction(Request $request, $status)
    {
        /* @var $pageManager \PW\CmsBundle\Model\PageManager */
        $pageManager = $this->get('pw_cms.page_manager');
        $qb = $pageManager->getRepository()->findByStatus($status);

        /* @var $paginator \Knp\Component\Pager\Paginator */
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate($qb,
            $request->query->get('page', 1),
            $request->query->get('pagesize', 15)
        );

        return array(
            'pages'  => $pagination,
            'status' => $status,
        );
    }

    /**
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/cms/page/new", name="admin_cms_page_new")
     * @Template
     */
    public function newAction(Request $request)
    {
        /* @var $pageManager \PW\CmsBundle\Model\PageManager */
        $pageManager = $this->get('pw_cms.page_manager');

        $form = $this->createForm(
            new CreatePageType(),
            new CreatePage()
        );

        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);
            if ($form->isValid()) {
                $formData = $form->getData();
                $page     = $formData->getPage();
                $pageManager->update($page);
                $this->get('session')->setFlash('success', "CMS Page saved successfully.");
            } else {
                $this->get('session')->setFlash('error', "There was an error while trying to save this CMS Page.");
            }
        }

        $result = array(
            'form' => $form->createView(),
            'form_path' => $this->generateUrl('admin_cms_page_new'),
        );
        return $result;
    }


    /**
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/cms/page/edit/{slug}",name="admin_cms_page_edit", requirements={"slug"=".+"})
     * @Template
     */
    public function editAction(Request $request, $slug)
    {
        /* @var $pageManager \PW\CmsBundle\Model\PageManager */
        $pageManager = $this->get('pw_cms.page_manager');

        $page = $pageManager->getRepository()->findOneBySlug($slug);
        $form = $this->createForm(
            new CreatePageType(),
            new CreatePage($page)
        );

        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);
            if ($form->isValid()) {
                $formData = $form->getData();
                $page     = $formData->getPage();
                $pageManager->update($page);
                $this->get('session')->setFlash('success', "CMS Page saved successfully.");
            } else {
                $this->get('session')->setFlash('success', "There was an error while trying to save this CMS Page.");
            }
        }

        $result = array(
            'form' => $form->createView(),
            'form_path' => $this->generateUrl('admin_cms_page_edit', array('slug' => $page->getSlug())),
        );

        return $result;
    }


}
