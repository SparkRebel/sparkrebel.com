<?php

namespace PW\TaggingBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\Security\Core\SecurityContext,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Route,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Template,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Method,
    JMS\SecurityExtraBundle\Annotation\Secure,
    PW\TaggingBundle\Document\Tagging,
    PW\TaggingBundle\Form\Type\TaggingType;

class TaggingController extends Controller
{
    /**
     * @Method("GET")
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/taggings", name="admin_taggings")
     * @Template
     */
    public function indexAction(Request $request)
    {

        $taggings = $this->get('doctrine_mongodb.odm.document_manager')
            ->getRepository('PWTaggingBundle:Tagging')
            ->findAll()
        ;
        
        return compact('taggings');
    }

    /**
     * @Method({"GET", "POST"})
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/taggings/new", name="admin_tagging_new")
     * @Template
     */
    public function newAction(Request $request)
    {

        $tagging  = new Tagging;       
        $form = $this->get('form.factory')->create(new TaggingType, $tagging);

        if ($request->getMethod() === 'POST') {
            $form->bindRequest($request);            
            if ($form->isValid()) {              
                $this->flushTagging($tagging);                      
                $this->get('session')->getFlashBag()->add('notice', 'Your tagging has been added successfully.');
                return $this->redirect($this->generateUrl('admin_taggings'));                            
            }
        }
        
        return array('form' => $form->createView(), 'form_path' => $this->generateUrl('admin_tagging_new'));
        
    }


    /**
     * @Method({"GET", "POST"})
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/taggings/edit/{id}", name="admin_tagging_edit")
     * @Template
     */
    public function editAction(Request $request, $id)
    {
        $tagging = $this->get('doctrine_mongodb.odm.document_manager')
            ->getRepository('PWTaggingBundle:Tagging')
            ->find($id)
        ;

        $form = $this->get('form.factory')->create(new TaggingType, $tagging);

        if ($request->getMethod() === 'POST') {
            $form->bindRequest($request);            
            if ($form->isValid()) {                          
                $this->flushTagging($tagging);          
                $this->get('session')->getFlashBag()->add('notice', 'Your tagging has been added successfully.');
                return $this->redirect($this->generateUrl('admin_taggings'));                            
            }
        }
        
        return array('form' => $form->createView(), 'form_path' => $this->generateUrl('admin_tagging_edit', array('id' => $tagging->getId())));                
    }

    /**
     * @Method("GET")
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/tagging/delete/{id}", name="admin_tagging_delete")
     * @Template
     */
    public function deleteAction(Request $request, $id)
    {
        $tagging = $this->get('doctrine_mongodb.odm.document_manager')
            ->getRepository('PWTaggingBundle:Tagging')
            ->find($id)
        ;
        $dm = $this->get('doctrine_mongodb.odm.document_manager');
        $dm->remove($tagging);
        $dm->flush();
        
        $this->get('session')->setFlash('success', sprintf("Successfully deleted tagging '%s'", $tagging->getName()));
        return $this->redirect($this->generateUrl('admin_taggings'));
    }

    protected function flushTagging(Tagging $t)
    {
        $dm = $this->get('doctrine_mongodb.odm.document_manager');
        $dm->persist($t);
        $dm->flush();
    }

   
}
