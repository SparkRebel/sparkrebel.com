<?php

namespace PW\OutfitBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB,
    Gedmo\Mapping\Annotation as Gedmo;

/**
 * Outfit Asset
 *
 * @MongoDB\EmbeddedDocument
 */
class OutfitAsset
{

    /**
     * id 
     * 
     * @MongoDB\String
     * @var mixed
     * @access protected
     */
    protected $id;

    /**
     * @MongoDB\ReferenceOne(targetDocument="PW\AssetBundle\Document\Asset")
     */
    protected $item;

    /**
     * x 
     * 
     * @MongoDB\Float
     * @var mixed
     * @access protected
     */
    protected $x;

    /**
     * y 
     * 
     * @MongoDB\Float
     * @var mixed
     * @access protected
     */
    protected $y;

    /**
     * scale 
     * 
     * @MongoDB\Float
     * @var mixed
     * @access protected
     */
    protected $scale;

    /**
     * Set id
     *
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get id
     *
     * @return string $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set item
     *
     * @param PW\ItemBundle\Document\Item $item
     */
    public function setItem(\PW\ItemBundle\Document\Item $item)
    {
        $this->item = $item;
    }

    /**
     * Get item
     *
     * @return PW\ItemBundle\Document\Item $item
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Set x
     *
     * @param float $x
     */
    public function setX($x)
    {
        $this->x = $x;
    }

    /**
     * Get x
     *
     * @return float $x
     */
    public function getX()
    {
        return $this->x;
    }

    /**
     * Set y
     *
     * @param float $y
     */
    public function setY($y)
    {
        $this->y = $y;
    }

    /**
     * Get y
     *
     * @return float $y
     */
    public function getY()
    {
        return $this->y;
    }

    /**
     * Set scale
     *
     * @param float $scale
     */
    public function setScale($scale)
    {
        $this->scale = $scale;
    }

    /**
     * Get scale
     *
     * @return float $scale
     */
    public function getScale()
    {
        return $this->scale;
    }
}
