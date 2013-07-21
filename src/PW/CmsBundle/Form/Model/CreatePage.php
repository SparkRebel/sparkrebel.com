<?php

namespace PW\CmsBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert,
    PW\CmsBundle\Document\Page;

class CreatePage
{
    /**
     * @Assert\Type(type="PW\CmsBundle\Document\Page")
     * @Assert\Valid
     */
    protected $page;

    /**
     * @param Page $page
     */
    public function __construct(Page $page = null)
    {
        $this->page = $page;
    }

    /**
     * @param Page $page
     */
    public function setPage(Page $page)
    {
        $this->page = $page;
    }

    /**
     * @return \PW\CmsBundle\Document\Page
     */
    public function getPage()
    {
        return $this->page;
    }
}
