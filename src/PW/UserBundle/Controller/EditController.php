<?php

namespace PW\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\Security\Core\SecurityContext,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Route,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Template,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Method,
    JMS\SecurityExtraBundle\Annotation\Secure,
    PW\UserBundle\Form\Model\EditUser,
    PW\UserBundle\Form\Type\EditUserType;

class EditController extends Controller
{
    /**
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/member/edit", name="user_profile_edit")
     * @Template
     */
    public function indexAction(Request $request)
    {
        /* @var $userManager \PW\UserBundle\Model\UserManager */
        $userManager = $this->get('pw_user.user_manager');

        $me   = $this->container->get('security.context')->getToken()->getUser();
        $form = $this->createForm(
            new EditUserType($me),
            new EditUser($me)
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
                $this->get('session')->setFlash('success', 'Profile updated successfully');
                return $this->redirect($this->generateUrl('user_profile_view_self'));
            }
            $this->get('session')->setFlash('error', 'There was a problem with the information you entered.');
        }

        return array(
            'user'      => $me,
            'form'      => $form->createView(),
            'form_path' => $this->generateUrl('user_profile_edit'),
        );
    }
}
