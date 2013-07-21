<?php

namespace PW\CategoryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpFoundation\Response,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Route,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Template,
    JMS\SecurityExtraBundle\Annotation\Secure;

class AreaController extends Controller
{

    /**
     * addBoardAction
     *
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/area/addBoard/{id}/{boardId}")
     *
     * @return response object
     */
    public function addBoardAction($id, $boardId)
    {
        $dm = $this->container->get('doctrine_mongodb.odm.document_manager');

        $areaRepo = $dm->getRepository('PWCategoryBundle:Area');
        $boardRepo = $dm->getRepository('PWBoardBundle:Board');

        $area = $areaRepo->find($id);
        $board = $boardRepo->find($boardId);

        if (empty($area) || empty($board)) {
            $result = array(
                'result' => 'error',
                'error' => "couldn't load either area or collection"
            );
        } else {
            $area->addBoards($board);

            $dm->persist($area);
            $dm->flush();

            $result = array(
                'result' => 'ok'
            );
        }



        $response = new Response(json_encode($result));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}
