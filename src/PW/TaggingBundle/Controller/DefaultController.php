<?php

namespace PW\TaggingBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use PW\BoardBundle\Document\Board;

class DefaultController extends Controller
{
    
    /**
     * @Method("POST")
     * @Secure(roles="ROLE_USER")
     * @Route("/taggings/new/{tagging}", name="new_tagging")
     */
    public function indexAction(Request $request, $tagging)
    {

        $tag = $this->get('doctrine_mongodb.odm.document_manager')
            ->getRepository('PWTaggingBundle:Tagging')
            ->find($tagging)
        ;
        
        $post = $this->get('doctrine_mongodb.odm.document_manager')
            ->getRepository('PWPostBundle:Post')
            ->find($request->get('id'))
        ;

        if (!$tag || !$post) {
            return new Response(json_encode(array('status' => 'error')));
        }

        $postManager = $this->get('pw_post.post_manager');
        $boardManager = $this->get('pw_board.board_manager');

        $me = $this->get('security.context')->getToken()->getUser();
        

        $board = $boardManager->findOrCreateBoard($tag->getName(), $me);
            
        $post  = $postManager->createRepost($post->getId(), $me);
        $post->setBoard($board);
        $postManager->save($post, array('validate' => false));
        return new Response(
            json_encode(
                array(
                    'status' => 'success', 
                    'path' =>   $this->generateUrl('pw_post_default_view', array('id' => $post->getId()))
                )
            )
        );
        
        
        
    }
    
}
