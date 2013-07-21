<?php

namespace PW\JobBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\Security\Core\SecurityContext,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Route,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Template,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Method,
    JMS\SecurityExtraBundle\Annotation\Secure;
 
use PW\UserBundle\Document\User,
    PW\JobBundle\Form\Model\CreateJob,
    PW\JobBundle\Form\Type\CreateJobType,
    PW\JobBundle\Document\Job;
    
/**
 * JobController
 *
 */
class JobController extends Controller
{

    /**
     * @Method("GET")
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/job", name="admin_job_index")
     * @Template
     */
    public function indexAction(Request $request)
    {
        /* @var $jobManager \PW\JobBundle\Model\JobManager */
        $jobManager = $this->get('pw_job.job_manager');
        
        $qb = $jobManager->getRepository()->findAll();

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate($qb,
            $request->query->get('page', 1),
            $request->query->get('pagesize', 15)
        );

        return array(
            'data' => $pagination,
        );
    }
    
    /**
     * @Method({"GET", "POST"})
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/job/new", name="admin_job_new")
     * @Template
     */
    public function newAction(Request $request)
    {
        /* @var $jobManager \PW\JobBundle\Model\JobManager */
        $jobManager = $this->get('pw_job.job_manager');
        $form = $this->createForm(
            new CreateJobType(),
            new CreateJob($jobManager->create())
        );
        $result = array(
            'form' => $form->createView(),
            'form_path' => $this->generateUrl('admin_job_store'),
            'job_board' => null
        );
        return $result;
    }

    
    /**
     * @Method({"GET", "POST"})
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/job/edit/{id}", name="admin_job_edit")
     * @Template
     */
    public function editAction(Request $request, $id)
    {
        /* @var $jobManager \PW\JobBundle\Model\JobManager */
        $jobManager = $this->get('pw_job.job_manager');
        
        $object = $jobManager->find($id);
        $form = $this->createForm(
            new CreateJobType(),
            new CreateJob($object)
        );

        $result = array(
            'object' => $object,
            'form' => $form->createView(),
            'form_path' => $this->generateUrl('admin_job_store'),
            'job_board' => $object->getBoard()
        );
        return $result;
    }
    
    /**
     * @Method({"POST"})
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/job/store", name="admin_job_store")
     * @Template
     */
    public function storeAction(Request $request)
    {
        $id = $request->get('id');
        
        /* @var $jobManager \PW\JobBundle\Model\JobManager */
        $jobManager = $this->get('pw_job.job_manager');
        
        $isNew = false;
        $object = $jobManager->find($id);
        if (!$object) {
            $isNew = true;
            $object = $jobManager->create();
        }
        $form = $this->createForm(
            new CreateJobType(),
            new CreateJob($object)
        );
    
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);
            if ($form->isValid()) {
                $formData = $form->getData();
                $object   = $formData->getJob();
                
                if ($isNew == true) {
                    // we create new Job so we need new Board for it
                    $jobManager->createBoardForJob($object, $request->request->get('new_collection_name'));
                }
                $jobManager->update($object);
                                
                $this->get('session')->setFlash('success', "Job saved successfully.");
            } else {
                $this->get('session')->setFlash('error', "There was an error while trying to save this Job.");
            }
        }
        
        // object created -> now redirect to editAction
        return $this->redirect($this->generateUrl('admin_job_edit', array('id' => $object->getId())));
    }

    /**
     * @Method({"GET", "POST"})
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/job/delete/{id}", name="admin_job_delete")
     * @Template
     */
    public function deleteAction(Request $request, $id)
    {
        /* @var $jobManager \PW\JobBundle\Model\JobManager */
        $jobManager = $this->get('pw_job.job_manager');
        
        $object = $jobManager->find($id);
        $me = $this->get('security.context')->getToken()->getUser();
        $jobManager->delete($object, $me);
        $this->get('session')->setFlash('success', "Job removed successfully.");
        
        // object removed -> now redirect to indexAction
        return $this->redirect($this->generateUrl('admin_job_index'));
    }

}
