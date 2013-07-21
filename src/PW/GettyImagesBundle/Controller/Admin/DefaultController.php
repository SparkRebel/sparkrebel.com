<?php

namespace PW\GettyImagesBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\Security\Core\SecurityContext,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Route,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Template,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Method,
    JMS\SecurityExtraBundle\Annotation\Secure;


class DefaultController extends Controller
{
    /**
     * @Method("GET")
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/getty/reports", name="admin_getty_reports")
     * @Template
     */
    public function indexAction(Request $request)
    {
        /* @var $manager \PW\GettyImagesBundle\Model\GettyReportManager */
        $manager = $this->get('pw_getty_images.getty_report_manager');
        
        $qb = $manager->getRepository()->findAll();

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate($qb,
            $request->query->get('page', 1),
            $request->query->get('pagesize', 9999)
        );
        
        
        return array(
            'data' => $pagination,
            'can_generate_new' => $manager->canGenerateNew()
        );
    }
    
    /**
     * @Method({"GET", "POST"})
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/getty/reports/new", name="admin_getty_report_new")
     * @Template
     */
    public function newAction(Request $request)
    {
        /* @var $manager \PW\GettyImagesBundle\Model\GettyReportManager */
        $manager = $this->get('pw_getty_images.getty_report_manager');
        
        if (!$manager->canGenerateNew()) {
            $this->get('session')->setFlash('error', "You cant generate new Getty Report (there is one in progress - not sent yet).");
            return $this->redirect($this->generateUrl('admin_getty_reports'));
        }
    
        $this->host = $this->container->getParameter('host');
        $dir = '..';
        if ($this->host == 'sparkrebel.com') {
            $dir = '/var/www/sparkrebel.com/current';
        } else {
            die('Cant send Getty Reports here...');
        } 
        
        $object = $manager->create();
        $manager->update($object);
        
        $command = "cd $dir && php app/console leezy:pheanstalk:put assets '\"getty:report:generate --env=prod\"' high 0 0 primary --env=prod";
        //$command = "cd $dir && php app/console getty:report:generate --env=prod >> /tmp/getty_report_log_".$object->getId().".log 2>&1";
        system($command);
        
        $this->get('session')->setFlash('success', "New Getty Report created successfully - starting to generate.");
        return $this->redirect($this->generateUrl('admin_getty_reports'));
    }
    
    /**
     * @Method({"GET", "POST"})
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/getty/reports/preview/{id}", name="admin_getty_report_preview")
     * @Template
     */
    public function previewAction(Request $request, $id)
    {
        /* @var $manager \PW\GettyImagesBundle\Model\GettyReportManager */
        $manager = $this->get('pw_getty_images.getty_report_manager');
        
        $object = $manager->find($id);
        $path = $object->getPreviewFilePath();
        if (!$path) {
            $this->get('session')->setFlash('error', "Cant view preview for this report yet.");
            return $this->redirect($this->generateUrl('admin_getty_reports'));
        }

        return new \Symfony\Component\HttpFoundation\Response(file_get_contents($path), 200, array(
            'Content-Type' => 'application/force-download',
            'Content-Disposition' => 'attachment; filename="'.basename($path).'"'
        ));
    }

    /**
     * @Method({"GET", "POST"})
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/getty/reports/send/{id}", name="admin_getty_report_send")
     * @Template
     */
    public function sendAction(Request $request, $id)
    {
        /* @var $manager \PW\GettyImagesBundle\Model\GettyReportManager */
        $manager = $this->get('pw_getty_images.getty_report_manager');
        
        $object = $manager->find($id);
        if (!$object->canSend()) {
            $this->get('session')->setFlash('error', "Cant send this report yet.");
            return $this->redirect($this->generateUrl('admin_getty_reports'));
        }
        
        $this->host = $this->container->getParameter('host');
        $dir = '..';
        if ($this->host == 'sparkrebel.com') {
            $dir = '/var/www/sparkrebel.com/current';
        } else {
            //die('Cant send Getty Reports here...');
        } 

        $command = "cd $dir && php app/console leezy:pheanstalk:put assets '\"getty:report:send --env=prod\"' high 0 0 primary --env=prod";
        //$command = "cd $dir && php app/console getty:report:send --env=prod >> /tmp/getty_report_log_".$object->getId().".log 2>&1";
        system($command);
        
        $this->get('session')->setFlash('success', "Getty Report starting to send.");
        return $this->redirect($this->generateUrl('admin_getty_reports'));
    }
}
