<?php

namespace PW\NewsletterBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\Security\Core\SecurityContext,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Route,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Template,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Method,
    JMS\SecurityExtraBundle\Annotation\Secure,
    PW\UserBundle\Document\User,
    PW\NewsletterBundle\Document\Newsletter,
    PW\NewsletterBundle\Form\Model\CreateNewsletter,
    PW\NewsletterBundle\Form\Type\CreateNewsletterType,
    PW\AssetBundle\Document\Asset,
    PW\NewsletterBundle\Document\NewsletterEmail;

class NewsletterController extends Controller
{
    /**
     * @Method("GET")
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/newsletter", name="admin_newsletter_index")
     * @Template
     */
    public function indexAction(Request $request)
    {
        /* @var $newsletterManager \PW\NewsletterBundle\Model\NewsletterManager */
        $newsletterManager = $this->get('pw_newsletter.newsletter_manager');
        $qb = $newsletterManager->getRepository()->findAllDesc();
        /* @var $paginator \Knp\Component\Pager\Paginator */
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate($qb,
            $request->query->get('newsletter', 1),
            $request->query->get('newslettersize', 15)
        );

        return array(
            'newsletters'  => $pagination
        );
    }

    /**
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/newsletter/new", name="admin_newsletter_new")
     * @Template
     */
    public function newAction(Request $request)
    {
        /* @var $newsletterManager \PW\NewsletterBundle\Model\NewsletterManager */
        $newsletterManager = $this->get('pw_newsletter.newsletter_manager');

        $dm   = $this->container->get('doctrine_mongodb.odm.document_manager');
        $user = $dm->getRepository('PWUserBundle:User')->findOneByUsername($this->container->getParameter('pw.system_user.sparkrebel.username'));

        //next Friday
        $nextFriday = new \DateTime('@'.strtotime( "next Friday" ), new \DateTimeZone('America/New_York'));
        $nextFriday->setTime(19,0);

        $newsletter = $newsletterManager->create();
        $newsletter->setSendAt($nextFriday);

        $createNewsletter = new CreateNewsletter();
        $createNewsletter->setNewsletter($newsletter);

        $type = new CreateNewsletterType();
        $type->setUser($user);
        $form = $this->createForm(
            $type,
            $createNewsletter
        );

        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);
            if ($form->isValid()) {
                $formData = $form->getData();
                $newsletter     = $formData->getNewsletter();
                $newsletter->setCreatedBy($this->getUser());

                $curatedTopImage = $this->addUpload($request, $form['newsletter'], 'curatedTopImage');
                if ($curatedTopImage) {
                    $newsletter->setCuratedTopImage($curatedTopImage);
                }

                $curatedBottomImage = $this->addUpload($request, $form['newsletter'], 'curatedBottomImage');
                if ($curatedBottomImage) {
                    $newsletter->setCuratedBottomImage($curatedBottomImage);
                }

                $newsletterManager->update($newsletter);
                $this->get('session')->setFlash('success', "Newsletter saved successfully.");

                return $this->redirect($this->generateUrl('admin_newsletter_edit', array('slug' => $newsletter->getSlug())));

            } else {
                $this->get('session')->setFlash('error', "There was an error while trying to save this Newsletter.");
            }
        }

        $result = array(
            'form' => $form->createView(),
            'form_path' => $this->generateUrl('admin_newsletter_new'),
        );
        return $result;
    }


    /**
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/newsletter/edit/{slug}",name="admin_newsletter_edit", requirements={"slug"=".+"})
     * @Template
     */
    public function editAction(Request $request, $slug)
    {
        /* @var $newsletterManager \PW\NewsletterBundle\Model\NewsletterManager */
        $newsletterManager = $this->get('pw_newsletter.newsletter_manager');

        $dm   = $this->container->get('doctrine_mongodb.odm.document_manager');
        $user = $dm->getRepository('PWUserBundle:User')->findOneByUsername($this->container->getParameter('pw.system_user.sparkrebel.username'));

        $newsletter = $newsletterManager->getRepository()->findOneBySlug($slug);

        $type = new CreateNewsletterType();
        $type->setUser($user);
        $form = $this->createForm(
            $type,
            new CreateNewsletter($newsletter)
        );

        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);
            if ($form->isValid()) {
                $formData = $form->getData();
                $newsletter     = $formData->getNewsletter();
                $newsletter->setCreatedBy($this->getUser());

                $curatedTopImage = $this->addUpload($request, $form['newsletter'], 'curatedTopImage');

                if ($curatedTopImage) {
                    $newsletter->setCuratedTopImage($curatedTopImage);
                }

                $curatedBottomImage = $this->addUpload($request, $form['newsletter'], 'curatedBottomImage');
                if ($curatedBottomImage) {
                    $newsletter->setCuratedBottomImage($curatedBottomImage);
                }

                $newsletterManager->update($newsletter);
                $this->get('session')->setFlash('success', "Newsletter saved successfully.");

                return $this->redirect($this->generateUrl('admin_newsletter_edit', array('slug' => $newsletter->getSlug())));
            } else {
                $this->get('session')->setFlash('success', "There was an error while trying to save this Newsletter.");
            }
        }

        $result = array(
            'form' => $form->createView(),
            'newsletter' => $newsletter,
            'form_path' => $this->generateUrl('admin_newsletter_edit', array('slug' => $newsletter->getSlug())),
        );

        return $result;
    }

    /**
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/newsletter/delete/{slug}",name="admin_newsletter_delete", requirements={"slug"=".+"})
     * @Template
     */
    public function deleteAction(Request $request, $slug)
    {
        /* @var $newsletterManager \PW\NewsletterBundle\Model\NewsletterManager */
        $newsletterManager = $this->get('pw_newsletter.newsletter_manager');

        $newsletter = $newsletterManager->getRepository()->findOneBySlug($slug);

        $newsletterManager->getDocumentManager()->remove($newsletter);
        $newsletterManager->flush();

        $this->get('session')->setFlash('success', "Newsletter deleted successfully.");

        return $this->redirect($this->generateUrl('admin_newsletter_index'));
    }

    /**
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/newsletter/commit/{slug}",name="admin_newsletter_commit", requirements={"slug"=".+"})
     * @Template
     */
    public function commitAction(Request $request, $slug)
    {
        /* @var $newsletterManager \PW\NewsletterBundle\Model\NewsletterManager */
        $newsletterManager = $this->get('pw_newsletter.newsletter_manager');

        $newsletter = $newsletterManager->getRepository()->findOneBySlug($slug);

        $newsletter->setStatus('pending');
        $newsletterManager->flush();

        $this->get('session')->setFlash('success', "Newsletter marked as pending successfully.");

        return $this->redirect($this->generateUrl('admin_newsletter_index'));
    }

    /**
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/newsletter/test/{slug}",name="admin_newsletter_test", requirements={"slug"=".+"})
     * @Template
     */
    public function testAction(Request $request, $slug)
    {
        /* @var $newsletterManager \PW\NewsletterBundle\Model\NewsletterManager */
        $newsletterManager = $this->get('pw_newsletter.newsletter_manager');

        $newsletter = $newsletterManager->getRepository()->findOneBySlug($slug);

        $testEmail = $this->getRequest()->request->get('testEmail', false);
        $justPreview = $this->getRequest()->request->get('justPreview', false);

        if($testEmail) {
            $dm = $this->get('doctrine_mongodb.odm.document_manager');
            $user = $dm->getRepository('PWUserBundle:User')->findOneByEmail($testEmail);

            if($newsletter && $user) {

                //$return = $newsletterManager->getMailer()->send($newsletter, $user, $justPreview);


                $command = "newsletter:send-one --userId={$user->getId()} --newsletter={$newsletter->getId()}";
                $return = $this->get('pw.event')->requestJob($command, 'normal', 'newsletter', '', 'feeds');

                if($return instanceof NewsletterEmail) // never gonna happen
                {
                    $previewUrl = $this->get('router')->generate('newsletter_view', array('code' => $return->getCode()), true);
                    $this->get('session')->setFlash('success', "Newsletter preview: <a target=\"_blank\" href=\"".$previewUrl."\">".$previewUrl."</a>");
                }
                elseif($return) {
                    $this->get('session')->setFlash('success', "Newsletter was sent successfully to email ".$testEmail);
                }
                else {
                    $this->get('session')->setFlash('error', "Newsletter could not be sent to email ".$testEmail." due to mailer error.");
                }
            }
            else {
                $this->get('session')->setFlash('error', "Newsletter could not be sent to email ".$testEmail." due to missing user.");
            }
        }
        else {
            $this->get('session')->setFlash('error', "Please wait until the entire list page loads on browser side and then press Test link. Test email should be entered in the popup.");
        }

        return $this->redirect($this->generateUrl('admin_newsletter_index'));
    }

    /**
     * @Secure(roles="ROLE_ADMIN")
     * @Route("/admin/newsletter/send/{slug}",name="admin_newsletter_send", requirements={"slug"=".+"})
     * @Template
     */
    public function sendAction(Request $request, $slug)
    {
        /* @var $newsletterManager \PW\NewsletterBundle\Model\NewsletterManager */
        $newsletterManager = $this->get('pw_newsletter.newsletter_manager');

        $newsletter = $newsletterManager->getRepository()->findOneBySlug($slug);

        $interval = $this->getRequest()->request->get('interval', false);
        $resend = $this->getRequest()->request->has('resend');

        if($interval) {
            $command = 'newsletter:send --newsletter=\''.$newsletter->getId().'\' --start=\''. $interval['start'] .'\' --end=\''. $interval['end'].'\''.($resend?' --resend':'');

            $message = "Newsletter was queued in Workers, for users from `".$interval['start']."` to `".$interval['end']."` interval".($resend?', with resending to already sent users':'').".";
        }
        else {
            $command = 'newsletter:send --newsletter=\''.$newsletter->getId().'\''.($resend?' --resend':'');

            $message = "Newsletter was queued in Workers, for all users".($resend?', with resending to already sent users':'').".";
        }
        
        $this->get('pw.event')->requestJob($command, 'normal', 'newsletter', '', 'feeds');

        $this->get('session')->setFlash('success', $message);

        return $this->redirect($this->generateUrl('admin_newsletter_index'));
    }

    /**
     * Save an uploaded file into web
     *
     * @param Request $request The Requet object
     * @param mixed   $form    Form
     * @param string  $field   The field name of the file
     *
     * @return \PW\AssetBundle\Document\Asset
     */
    public function addUpload(Request $request, $form, $field)
    {
        if (empty($form) || empty($form[$field])) {
            return false;
        }

        $newDir = 'images/newsletter/';

        /* @var $file \Symfony\Component\HttpFoundation\File\UploadedFile */
        $file = $form[$field]->getData();
        if (empty($file)) {
            return null;
        }

        $extension = explode('.',$file->getClientOriginalName());
        $extension = strtolower($extension[count($extension)-1]);
        $newName = uniqid(str_replace('.','-',uniqid('', TRUE))).'.'.$extension;

        $file->move($newDir, $newName);

        $params['source'] = 'upload';
        $params['url']    = $newName;

        $path = '/'.$newDir.$newName;

        $this->dm = $this->get('doctrine_mongodb.odm.document_manager');
        $repo = $this->dm->getRepository('PWAssetBundle:Asset');

        $webDir = $this->get('kernel')->getRootDir() . '/../web';

        if (!file_exists($path)) {
            $path = $webDir . $path;
        }

        if (empty($params['hash'])) {
            $params['hash'] = sha1_file($path);
        }

        $doc = $repo->findOneByHash($params['hash']);

        if ($doc) {
            $url = $doc->getUrl();
            $urlIsLocal = ($url[0] === '/');
            if (!$urlIsLocal) {
                return $doc;
            }
        }

        if (!$doc) {
            $doc = new Asset;
            $doc->setHash($params['hash']);
        }
        foreach ($params as $key => $value) {
            if (is_callable(array($doc, 'set' . $key))) {
                $doc->{'set' . $key}($value);
            }
        }

        $doc->setUrl('/'.$newDir.$newName);
        $doc->setisActive(true);

        $this->dm->persist($doc);
        $this->dm->flush();

        return $doc;
    }
}
