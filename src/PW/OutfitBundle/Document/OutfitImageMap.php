<?php

namespace PW\OutfitBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB,
    Gedmo\Mapping\Annotation as Gedmo;

/**
 * @MongoDB\EmbeddedDocument
 */
class OutfitImageMap
{

    /**
     * html
     * 
     * @MongoDB\String
     * @var mixed
     * @access protected
     */
    protected $html;

    /**
     * modified 
     * 
     * @Gedmo\Timestampable(on="update")
     * @MongoDB\Date
     * @access protected
     */
    protected $modified;

    /**
     * Set html
     *
     * @param string $html
     */
    public function setHtml($html)
    {
        $this->html = $html;
    }

    /**
     * Get html
     *
     * @return string $html
     */
    public function getHtml()
    {
        return $this->html;
    }

    /**
     * Set modified
     *
     * @param date $modified
     */
    public function setModified($modified)
    {
        $this->modified = $modified;
    }

    /**
     * Get modified
     *
     * @return date $modified
     */
    public function getModified()
    {
        return $this->modified;
    }
}
