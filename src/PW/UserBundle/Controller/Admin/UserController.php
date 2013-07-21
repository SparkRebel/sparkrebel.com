<?php

namespace PW\UserBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\Security\Core\SecurityContext,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Route,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Template,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Method,
    JMS\SecurityExtraBundle\Annotation\Secure,
    PW\UserBundle\Document\User,
    PW\UserBundle\Document\Brand,
    PW\UserBundle\Document\Merchant,
    PW\UserBundle\Form\Type\CreateUserType,
    PW\UserBundle\Form\Model\CreateUser,
    PW\UserBundle\Form\Type\EditUserType,
    PW\UserBundle\Form\Model\EditUser;

class UserController extends Controller
{
    /**
     * @Method("GET")
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/users/{type}/{status}", defaults={"type"="user", "status"="all"}, name="admin_user_index")
     * @Template
     */
    public function indexAction(Request $request, $type, $status)
    {
        /* @var $userManager \PW\UserBundle\Model\UserManager */
        $userManager = $this->get('pw_user.user_manager');
        $qb = $userManager->getRepository()->findByType($type, $status, false);

        /* @var $paginator \Knp\Component\Pager\Paginator */
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate($qb,
            $request->query->get('page', 1),
            $request->query->get('pagesize', 15)        
        );

        return array(
            'users'  => $pagination,
            'type'   => $type,
            'status' => $status,
        );
    }

    /**
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/user/create", name="admin_user_create")
     * @Template
     */
    public function createAction(Request $request)
    {
        /* @var $userManager \PW\UserBundle\Model\UserManager */
        $userManager = $this->get('pw_user.user_manager');

        // Handle User type switching
        $user = null;
        if ($request->request->has('pw_user_create')) {
            $formData = $request->request->get('pw_user_create');
            switch ($formData['type']) {
                case 'brand':
                    $user = new Brand();
                    break;
                case 'merchant':
                    $user = new Merchant();
                    break;
            }
        }

        $form = $this->createForm(
            new CreateUserType(),
            new CreateUser($user)
        );

        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);
            if ($form->isValid()) {
                $formData = $form->getData();
                $user = $formData->getUser();
                if ($formData->getNewIcon()) {
                    $asset = $this->get('pw.asset')->addUploadedFile($formData->getNewIcon());
                    $user->setIcon($asset);
                }
                $user->setEnabled(true);
                $userManager->update($user);
                $this->get('session')->setFlash('success', 'User created successfully: ' . $user->getUsernameCanonical());
                return $this->redirect($this->generateUrl('admin_user_edit', array('id' => $user->getId())));
            } else {
                $this->get('session')->setFlash('error', "There was a problem with the information you entered.");
            }
        }

        return array(
            'form'      => $form->createView(),
            'form_path' => $this->generateUrl('admin_user_create'),
        );
    }

    /**
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/user/edit/{id}", name="admin_user_edit")
     * @Template
     */
    public function editAction(Request $request, $id)
    {
        /* @var $userManager \PW\UserBundle\Model\UserManager */
        $userManager = $this->get('pw_user.user_manager');
        $user = $userManager->find($id);
        if (!$user) {
            throw $this->createNotFoundException("User not found");
        }

        $form = $this->createForm(
            new EditUserType($user, true),
            new EditUser($user)
        );

        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);
            if ($form->isValid()) {
                $formData = $form->getData();
                $user = $formData->getUser();
                if ($formData->getNewIcon()) {
                    $asset = $this->get('pw.asset')->addUploadedFile($formData->getNewIcon());
                    $user->setIcon($asset);
                }
                $userManager->update($user);
                $this->get('session')->setFlash('success', 'User updated successfully');
                return $this->redirect($this->generateUrl('admin_user_index'));
            }
            $this->get('session')->setFlash('error', 'User update failed');
        }

        return array(
            'user' => $user,
            'form' => $form->createView(),
            'form_path' => $this->generateUrl('admin_user_edit', array('id' => $user->getId())),
        );
    }

    /**
     * @Method("GET")
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/user/delete/{id}", name="admin_user_delete")
     * @Template
     */
    public function deleteAction(Request $request, $id)
    {
        /* @var $userManager \PW\UserBundle\Model\UserManager */
        $userManager = $this->get('pw_user.user_manager');
        $user = $userManager->find($id);
        if (!$user) {
            throw $this->createNotFoundException("User not found");
        }

        $me = $this->get('security.context')->getToken()->getUser();
        $userManager->delete($user, $me);

        $this->get('session')->setFlash('success', sprintf("Successfully deleted User '%s'", $user->getEmail()));
        return $this->redirect($this->generateUrl('admin_user_index'));
    }
    
    /**
     * @Method("GET")
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/user/undelete/{id}", name="admin_user_undelete")
     * @Template
     */
    public function undeleteAction(Request $request, $id)
    {
        /* @var $userManager \PW\UserBundle\Model\UserManager */
        $userManager = $this->get('pw_user.user_manager');
        $user = $userManager->find($id);
        if (!$user) {
            throw $this->createNotFoundException("User not found");
        }

        //$me = $this->get('security.context')->getToken()->getUser();
        $user->setDeleted(null);
        $user->setDeletedBy(null);
        $user->setEnabled(true);
        $user->setIsActive(true);
        $user->setRoles(array('ROLE_USER'));
        $userManager->update($user);
        
        $this->get('pw.event')->requestJob('stream:build ' . escapeshellarg($user->getId()), 'high', '', '', 'feeds');

        $this->get('session')->setFlash('success', sprintf("Successfully reactivated (undeleted) User '%s'", $user->getEmail()));
        return $this->redirect($this->generateUrl('admin_user_index'));
    }


    /**
     * @Method("POST")
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/toggle_curator_mode", name="admin_user_toggle_curator")
     */
    public function toggleCuratorAction(Request $request)
    {
        /* @var $userManager \PW\UserBundle\Model\UserManager */
        $userManager = $this->get('pw_user.user_manager');
        $me = $this->get('security.context')->getToken()->getUser();
        //$userManager->delete($user, $me);
        if($me->hasRole('ROLE_CURATOR')) {
            $me->removeRole('ROLE_CURATOR');
        } else {
            $me->addRole('ROLE_CURATOR');
        }
        $userManager->update($me);

        return new Response(json_encode(array('status' => 'ok')));
    }

    /**
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/user/buildStream/{id}", name="admin_user_build_stream")
     * @Template
     */
    public function requestBuildStreamAction(Request $request, $id)
    {
        /* @var $userManager \PW\UserBundle\Model\UserManager */
        $userManager = $this->get('pw_user.user_manager');
        $user = $userManager->find($id);
        if (!$user) {
            throw $this->createNotFoundException("User not found");
        }

        $this->get('pw.event')->requestJob('stream:build ' . $user->getId());
        return new Response(json_encode(array('status' => 'ok')));
    }
}
