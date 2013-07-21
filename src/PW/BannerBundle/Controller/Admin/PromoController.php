<?php

namespace PW\BannerBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\Security\Core\SecurityContext,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Route,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Template,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Method,
    JMS\SecurityExtraBundle\Annotation\Secure;
 
use PW\UserBundle\Document\User,
    PW\BannerBundle\Form\Model\CreatePromo,
    PW\BannerBundle\Form\Type\CreatePromoType,
    PW\AssetBundle\Document\Asset,
    PW\BannerBundle\Document\Promo;
    
/**
 * PromoController
 *
 */
class PromoController extends Controller
{

    /**
     * @Method("GET")
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/promo", name="admin_promo_index")
     * @Template
     */
    public function indexAction(Request $request)
    {
        /* @var $manager \PW\BannerBundle\Model\PromoManager */
        $manager = $this->get('pw_promo.promo_manager');
        
        $qb = $manager->getRepository()->findAll();

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate($qb,
            $request->query->get('page', 1),
            $request->query->get('pagesize', 9999)
        );

        return array(
            'data' => $pagination,
        );
    }
    
    /**
     * @Method({"GET", "POST"})
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/promo/new", name="admin_promo_new")
     * @Template
     */
    public function newAction(Request $request)
    {
        /* @var $manager \PW\BannerBundle\Model\PromoManager */
        $manager = $this->get('pw_promo.promo_manager');
        $object = $manager->create();
        $form = $this->createForm(
            new CreatePromoType(),
            new CreatePromo($object)
        );
        $result = array(
            'object' => $object,
            'form' => $form->createView(),
            'form_path' => $this->generateUrl('admin_promo_store')
        );
        return $result;
    }

    
    /**
     * @Method({"GET", "POST"})
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/promo/edit/{id}", name="admin_promo_edit")
     * @Template
     */
    public function editAction(Request $request, $id)
    {
        /* @var $manager \PW\BannerBundle\Model\PromoManager */
        $manager = $this->get('pw_promo.promo_manager');
        
        $object = $manager->find($id);
        $form = $this->createForm(
            new CreatePromoType(),
            new CreatePromo($object)
        );

        $result = array(
            'object' => $object,
            'form' => $form->createView(),
            'form_path' => $this->generateUrl('admin_promo_store')
        );
        return $result;
    }
    
    /**
     * @Method({"POST"})
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/promo/store", name="admin_promo_store")
     * @Template
     */
    public function storeAction(Request $request)
    {
        $id = $request->get('id');
        
        /* @var $manager \PW\BannerBundle\Model\PromoManager */
        $manager = $this->get('pw_promo.promo_manager');
        
        $isNew = false;
        $object = $manager->find($id);
        if (!$object) {
            $isNew = true;
            $object = $manager->create();
        }
        $form = $this->createForm(
            new CreatePromoType(),
            new CreatePromo($object)
        );
    
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);
            if ($form->isValid()) {
                $formData = $form->getData();
                $object   = $formData->getPromo();
                
                //$image = $this->addUpload($request, $form['promo'], 'bannerFile');
                $image = null;
                if (!empty($form['promo']) && $file = $form['promo']['bannerFile']->getData()) {
                    $extension = 'jpg';
                    if ($this->get('pw.asset')->isPngFile($file->getRealPath())) {
                        $extension = 'png';
                    }
                    $image = $this->get('pw.asset')->addUploadedFile($file, true, $extension);
                    unlink($file->getRealPath());
                }
                if ($image) {
                    $object->setImage($image);
                }

                $manager->update($object);
                                
                $this->get('session')->setFlash('success', "Promo saved successfully.");
            } else {
                $this->get('session')->setFlash('error', "There was an error while trying to save this Promo.");
            }
        }
        
        // object created -> now redirect to editAction
        return $this->redirect($this->generateUrl('admin_promo_edit', array('id' => $object->getId())));
    }
    
    /**
     * @Method({"GET", "POST"})
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/promo/delete/{id}", name="admin_promo_delete")
     * @Template
     */
    public function deleteAction(Request $request, $id)
    {
        /* @var $manager \PW\BannerBundle\Model\PromoManager */
        $manager = $this->get('pw_promo.promo_manager');
        
        $object = $manager->find($id);
        $me = $this->get('security.context')->getToken()->getUser();
        $manager->delete($object, $me);
        $this->get('session')->setFlash('success', "Promo removed successfully.");
        
        // object removed -> now redirect to indexAction
        return $this->redirect($this->generateUrl('admin_promo_index'));
    }

}
