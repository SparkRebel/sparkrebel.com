<?php

namespace PW\BannerBundle\Controller;

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
     * @Method("GET")
     * @Route("/promo/{id}/{slug}", defaults={"slug"=""}, requirements={"id"="[\da-f]{24}"}, name="promo_default_view")
     * @Template
     */
    public function viewAction($id, $next = false)
    {

        $dm    = $this->get('doctrine_mongodb.odm.document_manager');
        $promo = $dm->getRepository('PWBannerBundle:Promo')->find($id);

        if (!$promo) {
            return $this->redirect($this->generateUrl('home')); // wrong Promo -> go home
            //throw $this->createNotFoundException("Promo not found");
        } else if ($promo->getDeleted()) {
            return $this->redirect($this->generateUrl('home')); // Promo deleted -> go home
            throw $this->createNotFoundException("Promo has been removed");
        }

        /* @var $me \PW\UserBundle\Document\User */
        $me = $this->get('security.context')->getToken()->getUser();

        
        $qb = $dm->getRepository('PWCategoryBundle:Category')->createQueryBuilder()
            ->field('name')->equals(new \MongoRegex('/sales/i'));
        $promosCategory = $qb->getQuery()->execute()->getNext();
        if (!$promosCategory) {
            $qb = $dm->getRepository('PWCategoryBundle:Category')->createQueryBuilder()
            ->field('name')->equals(new \MongoRegex('/promos/i'));
            $promosCategory = $qb->getQuery()->execute()->getNext();
        }
        
        return array(
            'promo'          => $promo,
            'promosCategory' => $promosCategory
        );
    }
}
