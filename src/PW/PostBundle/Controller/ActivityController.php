<?php

namespace PW\PostBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpFoundation\Request,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Route,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Method,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Template,
    JMS\SecurityExtraBundle\Annotation\Secure,
    PW\PostBundle\Document\Post,
    PW\PostBundle\Document\PostComment,
    PW\PostBundle\Form\Type\CommentFormType,
    Gedmo\Sluggable\Util\Urlizer;

/**
 * @Route("/sparks")
 * @Route("/posts")
 */
class ActivityController extends Controller
{
    /**
     * @Method("POST")
     * @Secure(roles="ROLE_USER")
     * @Route("/comment/{id}/{replyTo}", defaults={"replyTo" = ""})
     *
     * @param Request $request
     * @param mixed   $id      The Post to Activity on
     * @param mixed   $replyTo The id of the Activity being replied to
     */
    public function commentAction(Request $request, $id, $replyTo)
    {
        $dm = $this->get('doctrine_mongodb.odm.document_manager');
        $me = $this->get('security.context')->getToken()->getUser();

        /* @var $post \PW\PostBundle\Document\Post */
        $post = $dm->getRepository('PWPostBundle:Post')->find($id);

        if (!$post) {
            throw $this->createNotFoundException("Post not found.");
        }

        $params = $request->get('pw_post_comment');

        $flood = $dm->createQueryBuilder('PWPostBundle:PostActivity')
            ->field('created')->gt(new \DateTime("1 minute ago"))
            ->field('post')->references($post)
            ->field('createdBy')->references($me)
            ->field('content')->equals($params['content'])
            ->getQuery()
            ->execute();

        if ($flood && $flood->count() > 0 ) {
            throw new \Exception('You seem to be sending the same comment over and over again. Sorry');
        }

        $comment = new PostComment();
        $comment->setCreatedBy($me);

        $form = $this->createForm(new CommentFormType(), $comment);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $dm->persist($comment);
            if ($replyTo) {
                //
                // Add comment as reply
                $activity = $dm->getRepository('PWPostBundle:PostActivity')->find($replyTo);
                $activity->addSubactivity($comment);

                //Attention: Post comment listener increments for comments, but repost does not keep reference to post, so wont be updated
                //$post->incCommentCount();
                $this->get('pw_post.post_manager')->save($post, array('validate' => false));
            } else {
                //
                // Add comment to Post activity
                $comment->setPost($post);
                $dm->persist($comment);

                $post->addActivity($comment);
                if ($post->getImage()->isGetty()) {
                    $this->get('pw.stats')
                        ->record('comment', $post->getImage(), $me, $this->get('request')->getClientIp());
                }
                $dm->persist($post);
            }
            $dm->flush();
            if ($request->isXmlHttpRequest()) {
                if ($replyTo) {
                    return $this->render('PWPostBundle:Activity:partials/subactivity.html.twig', array(
                        'subactivity' => $comment,
                        'showReplyForm' => isset($params['showReplyForm']) ? (bool) $params['showReplyForm'] : true,
                    ));
                } else {
                    $form = $this->createForm(new CommentFormType());
                    return $this->render('PWPostBundle:Activity:partials/activity.html.twig', array(
                        'activity' => $comment,
                        'me' => $me,
                        'post' => $post,
                        'form' => $form->createView(),
                        'formInstance' => $form,
                        'showReplyForm' => isset($params['showReplyForm']) ? (bool) $params['showReplyForm'] : true,
                    ));
                }
            } else {
                $this->get('session')->setFlash('success', 'Comment posted successfully');
                return $this->redirect($this->generateUrl('pw_post_default_view', array(
                    'id' => $post->getId(),
                    'slug' => Urlizer::transliterate($post->getDescription())
                )));
            }
        }
    }



    /**
     * @Secure(roles="ROLE_ADMIN")
     * @Method("DELETE")
     * @Route("/comment/{id}/delete", name="pw_post_comment_delete")
     *
     * @param Request $request
     * @param string $id
     */
    public function commentDeleteAction(Request $request, $id)
    {
      $postActivityManager = $this->get('pw_post.post_activity_manager');
      $comment = $postActivityManager->find($id);

      $me = $this->get('security.context')->getToken()->getUser();
      $postActivityManager->delete($comment, $me);
      return $this->redirect($request->headers->get('referer'));
    }

}
