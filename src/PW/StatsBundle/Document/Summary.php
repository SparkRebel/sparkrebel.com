<?php

namespace PW\StatsBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @MongoDB\Document(collection="Stats.summary")
 */
class Summary
{
    /**
     *  @MongoDB\Id(strategy="NONE")
     */
    protected $id;

    /**
     *  @MongoDB\String
     */
    protected $date;

    /**
     *  @MongoDB\Increment
     */
    protected $total;

    /**
     * @MongoDB\ReferenceOne(cascade={"persist"})
     */
    protected $reference;

    /**
     * Set id
     *
     * @param string $id
     * @return Summary
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
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
     * Set date
     *
     * @param string $date
     * @return Summary
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * Get date
     *
     * @return string $date
     */
    public function getDate()
    {
        return $this->date;
    }


    /**
     * Set total
     *
     * @param increment $total
     * @return Summary
     */
    public function setTotal($total)
    {
        $this->total = $total;
        return $this;
    }

    /**
     * Get total
     *
     * @return increment $total
     */
    public function getTotal()
    {
        return $this->total;
    }


    /**
     * Set reference
     *
     * @param $reference
     * @return Summary
     */
    public function setReference($reference)
    {
        $this->reference = $reference;
        return $this;
    }

    /**
     * Get reference
     *
     * @return $reference
     */
    public function getReference()
    {
        return $this->reference;
    }

}
