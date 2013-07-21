<?php

namespace PW\PostBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\Security\Core\SecurityContext,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Route,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Template,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Method,
    JMS\SecurityExtraBundle\Annotation\Secure,
    PW\UserBundle\Document\User,
    PW\PostBundle\Document\Post;

class PostController extends Controller
{
    /**
     * @Method("GET")
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/posts/{status}", defaults={"status"="all"}, name="admin_post_index")
     * @Template
     */
    public function indexAction(Request $request, $status)
    {
        /* @var $postManager \PW\PostBundle\Model\PostManager */
        $postManager = $this->get('pw_post.post_manager');
        $qb = $postManager->getRepository()->findByStatus($status);

        /* @var $paginator \Knp\Component\Postr\Paginator */
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate($qb,
            $request->query->get('page', 1),
            $request->query->get('pagesize', 15)
        );

        return array(
            'posts'  => $pagination,
            'status' => $status,
        );
    }

    /**
     * @Method("GET")
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/post/{type}/{id}", defaults={"type"="reposts"}, requirements={"type"="aggregate|reposts"}, name="admin_post_reposts")
     * @Template
     */
    public function repostsAction(Request $request, $type = null, $id)
    {
        /* @var $postManager \PW\PostBundle\Model\PostManager */
        $postManager = $this->get('pw_post.post_manager');
        $post = $postManager->find($id);
        if (!$post) {
            throw $this->createNotFoundException("Post not found");
        }

        if ($type == 'aggregate') {
            $qb = $postManager->getRepository()->findByOriginal($post);
        } else {
            $qb = $postManager->getRepository()->findByParent($post);
        }

        /* @var $paginator \Knp\Component\Postr\Paginator */
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate($qb,
            $request->query->get('page', 1),
            $request->query->get('pagesize', 15)
        );

        return array(
            'posts'  => $pagination,
            'parent' => $post,
            'type'   => $type,
        );
    }

    /**
     * @Method("GET")
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/post/delete/{id}", name="admin_post_delete")
     * @Template
     */
    public function deleteAction(Request $request, $id)
    {
        /* @var $postManager \PW\PostBundle\Model\PostManager */
        $postManager = $this->get('pw_post.post_manager');
        $post = $postManager->find($id);
        if (!$post) {
            throw $this->createNotFoundException("Post not found");
        }

        $me = $this->get('security.context')->getToken()->getUser();
        $postManager->delete($post, $me);

        $this->get('session')->setFlash('success', sprintf("Successfully deleted Post '%s'", $post->getDescription()));
        return $this->redirect($this->generateUrl('admin_post_index'));
    }
}
