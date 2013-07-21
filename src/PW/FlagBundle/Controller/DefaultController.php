<?php

namespace PW\FlagBundle\Controller;

use PW\FlagBundle\Document\Flag,
    Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Route,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * DefaultController
 */
class DefaultController extends Controller
{
    /**
     * Flag a post for admin attention
     *
     * @param object $request the request object
     * @param string $id      the id of the post being reported
     *
     * @Route("/flag/post/{id}")
     *
     * @return array
     */
    public function postAction(Request $request, $id)
    {
        $dm = $this->get('doctrine_mongodb.odm.document_manager');
        $post = $dm->getRepository('PWPostBundle:Post')->find($id);
        $data = $request->request->all();

        if (!$post) {
            $result = array('status' => 'ko');
            $response = new Response(json_encode($result));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

        $flag = new Flag();

        $flag->setIp($request->getClientIp());
        $flag->setTarget($post);
        $flag->setTargetUser($post->getCreatedBy());
        $flag->setType($data['type']);
        $flag->setUrl($data['url']);

        if ($data['type'] === 'copyright') {
            $flag->setDetails(array(
                'name' => $data['copyrightName'],
                'address' => $data['copyrightAddress'],
                'phone' => $data['copyrightPhone'],
                'email' => $data['copyrightEmail'],
                'original' => $data['copyrightOriginal'],
                'signature' => $data['copyrightSignature']
            ));
        }

        if (!empty($data['comments'])) {
            $flag->setReason($data['comments']);
        }

        if ($data['type'] === 'other') {
            $flag->setReason($data['otherSubject'] . "\n" . $data['comments']);
        }

        if (!empty($data['anonName'])) {
            $flag->setDetails(array(
                'name' => $data['anonName'],
                'email' => $data['anonEmail'],
            ));
        }

        $dm->persist($flag);
        $dm->flush();

        $result = array('status' => 'ok');
        $response = new Response(json_encode($result));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * Flag a comment
     *
     * @param object $request the request object
     * @param string $id      the id of the comment being reported
     *
     * @Route("/flag/comment/{id}")
     *
     * @return array
     */
    public function commentAction(Request $request, $id)
    {
        $dm = $this->get('doctrine_mongodb.odm.document_manager');
        $comment = $dm->getRepository('PWPostBundle:PostActivity')->find($id);
        $data = $request->request->all();

        if (!$comment) {
            $result = array('status' => 'ko');
            $response = new Response(json_encode($result));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

        $flag = new Flag();

        $flag->setIp($request->getClientIp());
        $flag->setTarget($comment);
        $flag->setTargetUser($comment->getCreatedBy());
        $flag->setType('comment');
        $flag->setUrl($data['url']);

        if (!empty($data['anonName'])) {
            $flag->setDetails(array(
                'name' => $data['anonName'],
                'email' => $data['anonEmail'],
            ));
        }
        if (!empty($data['comments'])) {
            $flag->setReason($data['comments']);
        }

        $dm->persist($flag);
        $dm->flush();

        $result = array('status' => 'ok');
        $response = new Response(json_encode($result));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * Returns the html used by the flag process - see flag.js
     *
     * @Route("/flag/templates")
     * @Template
     */
    public function templatesAction()
    {
        return array();
    }
}
