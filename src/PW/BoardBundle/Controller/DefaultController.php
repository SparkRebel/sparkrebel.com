<?php

namespace PW\BoardBundle\Controller;

use PW\ApplicationBundle\Controller\AbstractController,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Route,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Method,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Template,
    JMS\SecurityExtraBundle\Annotation\Secure,
    PW\BoardBundle\Document\Board,
    PW\BoardBundle\Form\Type\CreateBoardType,
    PW\BoardBundle\Form\Model\CreateBoard,
    PW\BoardBundle\Form\Type\EditBoardType,
    PW\BoardBundle\Form\Model\EditBoard,
    PW\PostBundle\Form\Type\CommentFormType,
    Symfony\Component\Security\Core\Exception\AccessDeniedException;

class DefaultController extends AbstractController
{
    /**
     * @Secure(roles="ROLE_USER")
     * @Route("/collection/add")
     * @Template
     */
    public function addAction(Request $request)
    {
        /* @var $boardManager \PW\BoardBundle\Model\BoardManager */
        $boardManager = $this->get('pw_board.board_manager');
        $result       = array('success' => false);

        /* @var $me \PW\UserBundle\Document\User */
        $me    = $this->get('security.context')->getToken()->getUser();
        $board = $boardManager->create(array('createdBy' => $me));
        $form  = $this->createForm(
            new CreateBoardType(),
            new CreateBoard($board)
        );

        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {
                $formData = $form->getData();
                $board    = $formData->getBoard();

                $boardManager->update($board);
                $result = array(
                    'success' => true,
                    'id'      => $board->getId(),
                    'name'    => $board->getName(),
                );
            } else {
                $result['error'] = $this->_getFirstErrorMessage($form);
            }

            if ($request->isXmlHttpRequest()) {
                // Check for a potential duplicate

                /*if ($duplicateBoard = $boardManager->getDuplicate($board)) {
                    $result['duplicate'] = true;
                    $result['id']        = $duplicateBoard->getId();
                    $result['name']      = $duplicateBoard->getName();
                }*/
                $response = new Response(json_encode($result));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            } else {
                if ($result['success']) {
                    $this->get('session')->setFlash('success', 'Collection created successfully');
                    return $this->redirect($this->generateUrl('pw_board_default_view', array(
                        'id' => $board->getId(),
                        'slug' => $board->getSlug(),
                    )));
                } else {
                    $this->get('session')->setFlash('error', 'Collection creation failed');
                }
            }
        }

        $result = array(
            'me'        => $me,
            'form'      => $form->createView(),
            'form_path' => $this->generateUrl('pw_board_default_add'),
        );

        if ($request->isXmlHttpRequest()) {
            return $this->render('PWBoardBundle:Default:partials/form.html.twig', $result);
        } else {
            return $result;
        }
    }

    /**
     * @Secure(roles="ROLE_USER")
     * @Route("/collection/edit/{id}/{render}", defaults={"render"=false}, requirements={"id"="[\da-f]{24}"})
     * @Template
     */
    public function editAction(Request $request, $id, $render = false)
    {
        /* @var $boardManager \PW\BoardBundle\Model\BoardManager */
        $boardManager = $this->get('pw_board.board_manager');
        $result       = array('success' => false);

        $me    = $this->get('security.context')->getToken()->getUser();
        $board = $boardManager->find($id);
        $form  = $this->createForm(new EditBoardType(), new EditBoard($board));

        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);
            if ($form->isValid()) {
                $formData = $form->getData();
                $boardManager->update($formData->getBoard());
                $result = array('success' => true);
                $this->get('session')->setFlash('success', 'Collection updated successfully');
            } else {
                if ($request->isXmlHttpRequest()) {
                    $result['error'] = $this->_getFirstErrorMessage($form);
                } else {
                    $this->get('session')->setFlash('error', 'Collection update failed');
                }
            }

            if ($request->isXmlHttpRequest()) {
                $response = new Response(json_encode($result));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }

            return $this->redirect($this->generateUrl('pw_board_default_view', array(
                'id' => $board->getId(),
                'slug' => $board->getSlug(),
            )));


            /*$this->redirect(
              $request->headers->get('referer')
            );*/
        }

        $result = array(
            'me'        => $me,
            'board'     => $board,
            'form'      => $form->createView(),
            'form_path' => $this->generateUrl('pw_board_default_edit', array('id' => $board->getId())),
        );

        if ($render || $request->isXmlHttpRequest()) {
            return $this->render('PWBoardBundle:Default:partials/form.html.twig', $result);
        } else {
            return $result;
        }
    }

    /**
     * @Method({"GET", "POST"})
     * @Route("/collection/{id}/{slug}.{_format}", defaults={"_format"="html", "slug"=""}, requirements={"id"="[\da-f]{24}", "_format"="html|json"})
     * @Template
     */
    public function viewAction($id, $next = false)
    {

        $dm    = $this->get('doctrine_mongodb.odm.document_manager');
        $board = $dm->getRepository('PWBoardBundle:Board')->find($id);

        if (!$board) {
            throw $this->createNotFoundException("Collection not found");
        } else if ($board->getDeleted()) {
            throw $this->createNotFoundException("Collection has been removed");
        }

        /* @var $followManager \PW\UserBundle\Model\FollowManager */
        $followManager = $this->container->get('pw_user.follow_manager');

        $isFollowing = false;
        $canPost     = false;
        $ownedByMe   = false;

        /* @var $me \PW\UserBundle\Document\User */
        $me = $this->get('security.context')->getToken()->getUser();

        $howManyCollectionsIsFollow = 0;
        if ($me instanceof \PW\UserBundle\Document\User) {
            $isFollowing = $followManager->isFollowing($me, $board);
            if ($board->getIsPublic() === true || $board->getCreatedBy()->getId() == $me->getId()) {
                $canPost = true;
            }
            $ownedByMe = $board->getCreatedBy()->getId() == $me->getId();
            $howManyCollectionsIsFollow = $followManager->getRepository()->findFollowingBoardsByUser($me)
                ->count()->getQuery()->execute();
        }

        $followersCount = $followManager->getRepository()
            ->findFollowersByTarget($board)
            ->hint(array('target.$ref' => 1, 'target.$id' => 1)) // point mongo to right index
            ->count()->getQuery()->execute();

        if ($board->getCreatedBy()->isCeleb()) {
            $this->get('pw.stats')
                ->record('view', $board, $me, $this->get('request')->getClientIp());
        }
        
        // get latest board posts to get images for Facebook "Like"
        $latestPosts = $dm->getRepository('PWPostBundle:Post')->findByBoard($board, array('limit'=>1))->getQuery()->execute();

        return array(
            'board'       => $board,
            'latestPosts'  => $latestPosts,
            'isFollowing' => $isFollowing,
            'canPost'     => $canPost,
            'ownedByMe'   => $ownedByMe,
            'followersCount' => $followersCount,
            'howManyCollectionsIsFollow' => $howManyCollectionsIsFollow
        );
    }

    /**
     * @Secure(roles="ROLE_USER")
     * @Method("DELETE")
     * @Route("/collection/delete/{id}", name="pw_board_delete")
     */
    public function boardDeleteAction(Request $request, $id)
    {
        $boardManager = $this->get('pw_board.board_manager');
        $board        = $boardManager->find($id);
        $me           = $this->get('security.context')->getToken()->getUser();

        if (!$board->wasCreatedBy($me)) {
            throw new AccessDeniedException();
        }

        $boardManager->delete($board, $me);
        return $this->redirect($this->generateUrl('pw_board_user_boardsuser'));
    }
}
