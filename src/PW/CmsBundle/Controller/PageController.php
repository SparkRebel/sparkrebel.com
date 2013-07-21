<?php

namespace PW\CmsBundle\Controller;

use PW\ApplicationBundle\Controller\AbstractController,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\Security\Core\SecurityContext,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Route,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Template,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Method,
    JMS\SecurityExtraBundle\Annotation\Secure,
    PW\PageBundle\Document\Page;

class PageController extends AbstractController
{
    /**
     * @Method("GET")
     * @Template
     */
    public function indexAction(Request $request, $id)
    {
        /* @var $pageManager \PW\CmsBundle\Model\PageManager */
        $pageManager = $this->get('pw_cms.page_manager');
        $page = $pageManager->find($id);

        $subsections = array();
        $section = $page->getSection();
        if ($section != '') {
            $pages = $pageManager->findBySection($section);
            foreach ($pages as $p) {
                $subsections[$p->getSubsection()] = $p;
            }
            
            uasort($subsections, function($a, $b) { return $a->getSubsectionOrder() - $b->getSubsectionOrder(); });
        }
        
        return array(
            'title'   => $page->getTitle(),
            'body' => $page->getContent(),
            
            'page' => $page,
            'section' => $page->getSection(),
            'subsection' => $page->getSubsection(),
            'subsections' => $subsections,
        );
    }
}
