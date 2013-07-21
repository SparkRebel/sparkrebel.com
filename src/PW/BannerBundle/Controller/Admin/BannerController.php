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
    PW\BannerBundle\Form\Model\CreateBanner,
    PW\BannerBundle\Form\Type\CreateBannerType,
    PW\AssetBundle\Document\Asset,
    PW\BannerBundle\Document\Banner;
    
/**
 * BannerController
 *
 */
class BannerController extends Controller
{

    /**
     * @Method("GET")
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/banner", name="admin_banner_index")
     * @Template
     */
    public function indexAction(Request $request)
    {
        /* @var $bannerManager \PW\BannerBundle\Model\BannerManager */
        $bannerManager = $this->get('pw_banner.banner_manager');
        
        $qb = $bannerManager->getRepository()->findAll();

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate($qb,
            $request->query->get('page', 1),
            $request->query->get('pagesize', 9999)
        );
        
        if (isset($_GET['test_asset_id'])) {
            $this->container->get('pw.event')->publish('asset.create', array('assetId' => $_GET['test_asset_id']),
                'high', 'assets', 'feeds'); 
        }

        return array(
            'data' => $pagination,
        );
    }
    
    /**
     * @Method({"GET", "POST"})
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/banner/new", name="admin_banner_new")
     * @Template
     */
    public function newAction(Request $request)
    {
        /* @var $bannerManager \PW\BannerBundle\Model\BannerManager */
        $bannerManager = $this->get('pw_banner.banner_manager');
        $form = $this->createForm(
            new CreateBannerType(),
            new CreateBanner($bannerManager->create())
        );
        $result = array(
            'form' => $form->createView(),
            'form_path' => $this->generateUrl('admin_banner_store')
        );
        return $result;
    }

    
    /**
     * @Method({"GET", "POST"})
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/banner/edit/{id}", name="admin_banner_edit")
     * @Template
     */
    public function editAction(Request $request, $id)
    {
        /* @var $bannerManager \PW\BannerBundle\Model\BannerManager */
        $bannerManager = $this->get('pw_banner.banner_manager');
        
        $object = $bannerManager->find($id);
        $form = $this->createForm(
            new CreateBannerType(),
            new CreateBanner($object)
        );

        $result = array(
            'object' => $object,
            'form' => $form->createView(),
            'form_path' => $this->generateUrl('admin_banner_store')
        );
        return $result;
    }
    
    /**
     * @Method({"POST"})
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/banner/store", name="admin_banner_store")
     * @Template
     */
    public function storeAction(Request $request)
    {
        $id = $request->get('id');
        
        /* @var $bannerManager \PW\BannerBundle\Model\BannerManager */
        $bannerManager = $this->get('pw_banner.banner_manager');
        
        $isNew = false;
        $object = $bannerManager->find($id);
        if (!$object) {
            $isNew = true;
            $object = $bannerManager->create();
        }
        $form = $this->createForm(
            new CreateBannerType(),
            new CreateBanner($object)
        );
    
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);
            if ($form->isValid()) {
                $formData = $form->getData();
                $object   = $formData->getBanner();
                
                //$image = $this->addUpload($request, $form['banner'], 'bannerFile');
                $image = null;
                if (!empty($form['banner']) && $file = $form['banner']['bannerFile']->getData()) {
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

                $bannerManager->update($object);
                                
                $this->get('session')->setFlash('success', "Banner saved successfully.");
            } else {
                $this->get('session')->setFlash('error', "There was an error while trying to save this Banner.");
            }
        }
        
        // object created -> now redirect to editAction
        return $this->redirect($this->generateUrl('admin_banner_edit', array('id' => $object->getId())));
    }    
    
    /**
     * @Method({"GET", "POST"})
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/banner/delete/{id}", name="admin_banner_delete")
     * @Template
     */
    public function deleteAction(Request $request, $id)
    {
        /* @var $bannerManager \PW\BannerBundle\Model\BannerManager */
        $bannerManager = $this->get('pw_banner.banner_manager');
        
        $object = $bannerManager->find($id);
        $me = $this->get('security.context')->getToken()->getUser();
        $bannerManager->delete($object, $me);
        $this->get('session')->setFlash('success', "Banner removed successfully.");
        
        // object removed -> now redirect to indexAction
        return $this->redirect($this->generateUrl('admin_banner_index'));
    }

}
