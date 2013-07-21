<?php

namespace PW\PostBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PW\PostBundle\Form\Type\CommentFormType;
use PW\UserBundle\Document\User;
use JMS\SecurityExtraBundle\Annotation\Secure;
use PW\BoardBundle\Document\Board;
use PW\BoardBundle\Form\Type\CreateBoardType;
use PW\BoardBundle\Form\Model\CreateBoard;
use PW\PostBundle\Form\Type\EditPostType;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Doctrine\ODM\MongoDB\DocumentNotFoundException;

class DefaultController extends Controller
{
    /**
     * Note: POST is allowed because of Facebook
     *
     * @Method({"GET", "POST"})
     * @Route("/", name="post_default_index")
     * @Template
     */
    public function indexAction(Request $request)
    {
        $me = $this->get('security.context')->getToken()->getUser();
        if ($me instanceOf User) {
            return array('type' => 'userStream', 'id' => $me->getId());
        } else {
            return array('type' => 'userAnon', 'id' => null);
        }
    }

    /**
     * @Method("GET")
     * @Route("/spark-comments/{id}")
     */
    public function commentsAction($id)
    {
        /* @var $postManager \PW\PostBundle\Model\PostManager */
        $postManager = $this->get('pw_post.post_manager');

        /* @var $postActivityManager \PW\PostBundle\Model\PostActivityManager */
        $postActivityManager = $this->get('pw_post.post_activity_manager');

        /* @var $post \PW\PostBundle\Document\Post */
        $post = $postManager->find($id);

        if (!$post) {
            throw $this->createNotFoundException("Spark not found");
        } elseif ($post->getDeleted()) {
            throw $this->createNotFoundException("Spark has been removed");
        }

        // Retrieve all post activity
        $activity = $postActivityManager->getRepository()
                ->findByPost($post)
                ->sort('created')
                ->getQuery()->execute();

        $out = array(
            'postActivity' => '',
            'numComments'  => count($activity),
        );

        $form = $this->createForm(new CommentFormType(true));

        foreach ($activity as $row) {
            $out['postActivity'] .= $this->render('PWPostBundle:Activity:partials/activity.html.twig', array(
                    'activity'      => $row,
                    'showReplyForm' => true,
                    'post'          => $post,
                    'form'          => $form->createView(),
                    'formInstance'  => $form,
                ))->getContent();
        }

        return new Response(json_encode($out));
    }

    /**
     * @Secure(roles="ROLE_USER")
     * @Method({"GET", "POST"})
     * @Route("/spark/edit/{id}", name="pw_post_edit")
     * @Template
     */
    public function editAction(Request $request, $id)
    {
        $postManager = $this->get('pw_post.post_manager');
        $post        = $postManager->find($id);
        $me          = $this->get('security.context')->getToken()->getUser();

        $me_updating = true;
        if (!$post->wasCreatedBy($me)) {
            $me_updating = false;
            if(!$this->get('security.context')->isGranted('ROLE_ADMIN') && !($this->get('security.context')->isGranted('ROLE_OPERATOR') && $post->getCreatedBy()->isCeleb())) {
                throw new AccessDeniedException('You dont have rights to that action');
            }
        }
        
        if ($me_updating) {
            $editPostType = new EditPostType($me);
        } else {
            $editPostType = new EditPostType($post->getCreatedBy());
        }

        $form      = $this->createForm($editPostType, $post);
        $boardForm = $this->createForm(
            new CreateBoardType(), new CreateBoard()
        );

        $original_board = $post->getBoard();
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);
            $boardForm->bindRequest($request);
            if ($form->isValid()) {
                $formData = $form->getData();
                // new board is submitted
                $new_board = $boardForm->getData()->getBoard();

                if (count($this->get('validator')->validate($new_board)) < 1) {
                    if($me_updating) {
                        $new_board->setCreatedBy($me);
                    } else {
                        $new_board->setCreatedBy($post->getCreatedBy());
                    }

                    $formData->setBoard($new_board);
                }
                $dm    = $this->get('doctrine_mongodb.odm.document_manager');
                $board = $formData->getBoard();
                // Detect if boards were changed and update boards images
                if ($original_board != $board) {
                    $board->addImages($post->getImage());
                    $board->incPostCount();
                    $dm->persist($board);
                    $original_board->removeImage($post->getImage());
                    $original_board->decrementPostCount();
                    $dm->persist($original_board);
                }
                $dm->flush();
                $postManager->update($formData);

                $this->get('session')->setFlash('success', 'Spark updated successfully');
                if ($targetUrl = $request->headers->get('Referer')) {
                    return $this->redirect($targetUrl);
                } else {
                    return $this->redirect($this->generateUrl('pw_post_default_view_1', array('id' => $post->getId())));
                }
            } else {
                if ($request->isXmlHttpRequest()) {
                    $result['error'] = $this->_getFirstErrorMessage($form);
                } else {
                    $this->get('session')->setFlash('error', 'Spark update failed');
                }
            }

            if ($request->isXmlHttpRequest()) {
                $response = new Response(json_encode($result));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }
        }

        $result = array(
            'me'        => $me,
            'post'      => $post,
            'boardForm' => $boardForm->createView(),
            'form'      => $form->createView(),
        );

        if ($request->isXmlHttpRequest()) {
            return $this->render('PWPostBundle:Default:partials/editForm.html.twig', $result);
        } else {
            return $result;
        }
    }

    /**
     * @Secure(roles="ROLE_USER")
     * @Method({"DELETE"})
     * @Route("/post/{id}/delete", name="pw_post_delete")
     */
    public function deleteAction(Request $request, $id)
    {
        $postManager = $this->get('pw_post.post_manager');
        $post        = $postManager->find($id);

        $me = $this->get('security.context')->getToken()->getUser();




        if (!$post->wasCreatedBy($me)) {
            if(!$this->get('security.context')->isGranted('ROLE_ADMIN') && !($this->get('security.context')->isGranted('ROLE_OPERATOR') && $post->getCreatedBy()->isCeleb())) {
                throw new AccessDeniedException();
            }

        }

        $board = $post->getBoard();

        $default_redirect_url = $this->generateUrl('pw_board_default_view', array(
            'id'   => $board->getId(),
            'slug' => $board->getSlug(),
        ));


        // we assume that referer is a GET page. Because current matcher have the delete method, we clone it and match the referer url against GET context
        $matcher = clone $this->get('router')->getMatcher();
        $context = $matcher->getContext();
        $context->setMethod('get');

        $referer_params = parse_url($request->headers->get('referer'));
        $arr = explode('/', $referer_params['path']);

        $path_info = '';
        if(strpos($arr[1], '.php') === false) {
            $to_match = $referer_params['path'];
        } else {
            unset($arr[1]);
            $to_match = implode('/', $arr);
        }


        try {
            $matches = $matcher->match($to_match);
            // if referer isnt spark view page, redirect to it. Else redirect to collection view, cuz referer would throw 404
            if($matches['_route'] != 'pw_post_default_view') {
                $default_redirect_url = $request->headers->get('referer');
            }
        } catch (\Exception $e) {

        }

        $postManager->delete($post, $me);
        return $this->redirect($default_redirect_url);
    }

    /**
     * @Method({"GET", "POST"})
     * @Route("/spark/{id}/{slug}", defaults={"slug" = ""})
     * @Route("/post/{id}/{slug}", defaults={"slug" = ""})
     * @Template
     */
    public function viewAction($id)
    {
        try {
            $me   = $this->get('security.context')->getToken()->getUser();
            $form = $this->createForm(new CommentFormType(true));

            /* @var $postManager \PW\PostBundle\Model\PostManager */
            $postManager = $this->get('pw_post.post_manager');

            /* @var $postActivityManager \PW\PostBundle\Model\PostActivityManager */
            $postActivityManager = $this->get('pw_post.post_activity_manager');

            /* @var $post \PW\PostBundle\Document\Post */
            $post = $postManager->find($id);

            if (!$post) {
                throw $this->createNotFoundException("Spark not found");
            } elseif ($post->getDeleted()) {
                throw $this->createNotFoundException("Spark has been removed");
            }

            // Retrieve all post activity
            $activity = $postActivityManager
                ->getRepository()
                ->findByPost($post)
                ->limit(10)
                ->sort('created desc')
                ->getQuery()
                ->execute()
            ;

            // More posts on the same board
            $allBoardPosts = $postManager->getRepository()
                ->findByBoard($post->getBoard());

            $allBoardPostsCount = $allBoardPosts->getQuery()->execute()->count();
            $allBoardPosts      = $allBoardPosts->limit(100)->getQuery()->execute();

            // More boards with this item
            $similarPosts = $postManager
                ->getRepository()
                ->findByTarget($post->getTarget())
                ->getQuery()
                ->execute()
            ;

            $allBoards = new \Doctrine\Common\Collections\ArrayCollection;
            //logic for allBoards:
            // - 1st the current board
            // - then the parent board, if exists
            // - then the original board, if exists
            // - then the rest of the boards

            if ($board = $post->getBoard()) {
                $allBoards[$post->getBoard()->getId()] = $post->getBoard();
            }

            $parent = $post->getParent();
            $parentBoard = $parent ? $parent->getBoard() : null;
            if ($parent && $parentBoard) {
                $allBoards[$parentBoard->getId()] = $parentBoard;
            }

            $original = $post->getOriginal();
            $originalBoard = $original ? $original->getBoard() : null;
            if ($original && $originalBoard) {
                $allBoards[$originalBoard->getId()] = $originalBoard;
            }

            foreach ($similarPosts as $sPost) {
                $sBoard = $sPost->getBoard();
                if ($sBoard && $sBoard->getIsActive()) {
                    $allBoards[$sBoard->getId()] = $sBoard;
                }
            }

            $index = true;
            if ($board && $board->getIsSystem() && $post->getTarget() instanceof \PW\ItemBundle\Document\Item) {
                $index = false;
            }

            $friends_who_resparked = null;

            if (true === $this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
                $base                  = $post->getOriginal() ? $post->getOriginal() : $post;
                $friends_who_resparked = $postManager->getFriendsWhoResparkedPostForUser($base, $this->get('security.context')->getToken()->getUser());
            }

            if ($post->getImage()->isGetty()) {
                if (!$this->get('pw.stats')->isHttpUserAgentBot()) {
                    $this->get('pw.stats')
                        ->record('view', $post->getImage(), $me, $this->get('request')->getClientIp());
                }
            }

            return array(
                'index'                 => $index, // meta index or noindex flag
                'me'                    => $me,
                'post'                  => $post,
                'activity'              => array_reverse(iterator_to_array($activity)),
                'allBoardPosts'         => $allBoardPosts,
                'allBoardPostsCount'    => $allBoardPostsCount,
                'allBoards'             => $allBoards,
                'form'                  => $form->createView(),
                'formInstance'          => $form,
                'friends_who_resparked' => $friends_who_resparked
            );

        } catch (DocumentNotFoundException $e) {
            throw $this->createNotFoundException("One of this Spark's related items has been removed.");
        }
    }
}
