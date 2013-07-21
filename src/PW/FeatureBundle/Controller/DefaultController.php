<?php

namespace PW\FeatureBundle\Controller;

use PW\FeatureBundle\Document\Feature,
    Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpFoundation\Request,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Route,
    JMS\SecurityExtraBundle\Annotation\Secure;

/**
 * DefaultController
 */
class DefaultController extends Controller
{

    /**
     * addBoardAction
     *
     * @param mixed $id The id of the board to feature
     *
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/feature/board/{id}", requirements={"id" = "[a-f0-9]{24}"})
     *
     * @return response object
     */
    public function addBoardAction(Request $request, $id)
    {
        $dm = $this->container->get('doctrine_mongodb.odm.document_manager');

        $featureRepo = $dm->getRepository('PWFeatureBundle:Feature');
        $boardRepo = $dm->getRepository('PWBoardBundle:Board');

        $feature = $featureRepo
            ->findOneBy(
                array(
                    'target.$id' => new \MongoId($id),
                    'isActive' => true
                )
            );

        if (!$feature) {
            $feature = new Feature();
            $feature->setIsActive(true);
            $board = $boardRepo->find($id);
            $feature->setTarget($board);

            $data = $request->query->all();
            if (!empty($data)) {
                $feature->fromArray($data);
            }

            $dm->persist($feature);
            $dm->flush();
        }

        $result = array(
            'result' => 'ok'
        );

        $response = new Response(json_encode($result));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * addBrandAction
     *
     * @param mixed $id The id of the brand to feature
     *
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/feature/brand/{id}", requirements={"id" = "[a-f0-9]{24}"})
     *
     * @return response object
     */
    public function addBrandAction(Request $request, $id)
    {
        $dm = $this->container->get('doctrine_mongodb.odm.document_manager');

        $featureRepo = $dm->getRepository('PWFeatureBundle:Feature');
        $userRepo = $dm->getRepository('PWUserBundle:User');

        $feature = $featureRepo->findOneActiveBetweenDates($id, $request->query->get('start'), $request->query->get('end'))->getQuery()->getSingleResult();

        if (!$feature) {
            $feature = new Feature();
            $feature->setIsActive(true);
            $brand = $userRepo->findOneBy(
                    array(
                        '_id' => new \MongoId($id),
                        'type' => array('$ne' => 'user')
                )
            );
            $feature->setTarget($brand);

            $data = $request->query->all();
            if (!empty($data)) {
                $feature->fromArray($data);
            }

            $dm->persist($feature);
            $dm->flush();

            $result['result'] = true;
            $result['message'] = 'Brand feature was added successfully!';
        }
        else {
            $result['result'] = false;
            $result['message'] = 'Brand feature was not added because it is already active in this period!';
        }

        $response = new Response(json_encode($result));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}
