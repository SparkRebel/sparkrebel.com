<?php

namespace PW\CmsBundle\Model;

use PW\ApplicationBundle\Model\AbstractManager,
    PW\CmsBundle\Document\Page,
    PW\UserBundle\Document\User;

class PageManager extends AbstractManager
{
    /**
     * @param array $data
     * @return \PW\CmsBundle\Document\Page
     */
    public function create(array $data = array())
    {
        /* @var $page \PW\CmsBundle\Document\Page */
        $page = parent::create($data);
        return $page;
    }

    /**
     * @return mixed
     */
    public function findAllActive()
    {
        return $this->getRepository()->findAllActive()->getQuery()->execute();
    }
    
    /**
     * @param string $section
     * @return mixed
     */
    public function findBySection($section) {
        return $this->getRepository()->findBySection($section)->getQuery()->execute();
    }
}
