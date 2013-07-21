<?php

namespace PW\FlagBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB,
    Gedmo\Mapping\Annotation as Gedmo,
    PW\ApplicationBundle\Document\AbstractDocument;

/**
 * @MongoDB\EmbeddedDocument
 */
class FlagSummary extends AbstractDocument
{
    /**
     * @var int
     * @MongoDB\Int
     */
    protected $totalBy;

    /**
     * @var int
     * @MongoDB\Int
     */
    protected $totalByApproved;

    /**
     * @var int
     * @MongoDB\Int
     */
    protected $totalByRejected;

    /**
     * @var int
     * @MongoDB\Int
     */
    protected $totalAgainst;

    /**
     * @var int
     * @MongoDB\Int
     */
    protected $totalAgainstApproved;

    /**
     * @var int
     * @MongoDB\Int
     */
    protected $totalAgainstRejected;

    /**
     * @var bool
     * @MongoDB\NotSaved
     */
    protected $isActive;

    /**
     * @var bool
     * @MongoDB\NotSaved
     */
    protected $deleted;

    public function __construct()
    {
        $this->totalBy = 0;
        $this->totalByApproved = 0;
        $this->totalByRejected = 0;
        $this->totalAgainst = 0;
        $this->totalAgainstApproved = 0;
        $this->totalAgainstRejected = 0;
    }

    /**
     * @return int
     */
    public function incTotalBy()
    {
        return ++$this->totalBy;
    }

    /**
     * @return int
     */
    public function incTotalByApproved()
    {
        return ++$this->totalByApproved;
    }

    /**
     * @return int
     */
    public function incTotalByRejected()
    {
        return ++$this->totalByRejected;
    }

    /**
     * @return int
     */
    public function incTotalAgainst()
    {
        return ++$this->totalBy;
    }

    /**
     * @return int
     */
    public function incTotalAgainstApproved()
    {
        return ++$this->totalByApproved;
    }

    /**
     * @return int
     */
    public function incTotalAgainstRejected()
    {
        return ++$this->totalByRejected;
    }

    /**
     * @return string
     */
    public function getAdminValue()
    {
        $by = $this->getTotalBy();
        $byA = $this->getTotalByApproved();
        $byR = $this->getTotalByRejected();

        if ($by) {
            $by = "$byA/$byR/$by";
        } else {
            $by = "No flags";
        }

        $ag = $this->getTotalAgainst();
        $agA = $this->getTotalAgainstApproved();
        $agR = $this->getTotalAgainstRejected();

        if ($ag) {
            $ag = "$agA/$agR/$ag";
        } else {
            $ag = "No flags";
        }

        return "By: $by. Against: $ag";
    }

    //
    // Doctrine Generation Below
    //

    /**
     * Set totalBy
     *
     * @param increment $totalBy
     */
    public function setTotalBy($totalBy)
    {
        $this->totalBy = $totalBy;
    }

    /**
     * Get totalBy
     *
     * @return increment $totalBy
     */
    public function getTotalBy()
    {
        return $this->totalBy;
    }

    /**
     * Set totalByApproved
     *
     * @param increment $totalByApproved
     */
    public function setTotalByApproved($totalByApproved)
    {
        $this->totalByApproved = $totalByApproved;
    }

    /**
     * Get totalByApproved
     *
     * @return increment $totalByApproved
     */
    public function getTotalByApproved()
    {
        return $this->totalByApproved;
    }

    /**
     * Set totalByRejected
     *
     * @param increment $totalByRejected
     */
    public function setTotalByRejected($totalByRejected)
    {
        $this->totalByRejected = $totalByRejected;
    }

    /**
     * Get totalByRejected
     *
     * @return increment $totalByRejected
     */
    public function getTotalByRejected()
    {
        return $this->totalByRejected;
    }

    /**
     * Set totalAgainst
     *
     * @param increment $totalAgainst
     */
    public function setTotalAgainst($totalAgainst)
    {
        $this->totalAgainst = $totalAgainst;
    }

    /**
     * Get totalAgainst
     *
     * @return increment $totalAgainst
     */
    public function getTotalAgainst()
    {
        return $this->totalAgainst;
    }

    /**
     * Set totalAgainstApproved
     *
     * @param increment $totalAgainstApproved
     */
    public function setTotalAgainstApproved($totalAgainstApproved)
    {
        $this->totalAgainstApproved = $totalAgainstApproved;
    }

    /**
     * Get totalAgainstApproved
     *
     * @return increment $totalAgainstApproved
     */
    public function getTotalAgainstApproved()
    {
        return $this->totalAgainstApproved;
    }

    /**
     * Set totalAgainstRejected
     *
     * @param increment $totalAgainstRejected
     */
    public function setTotalAgainstRejected($totalAgainstRejected)
    {
        $this->totalAgainstRejected = $totalAgainstRejected;
    }

    /**
     * Get totalAgainstRejected
     *
     * @return increment $totalAgainstRejected
     */
    public function getTotalAgainstRejected()
    {
        return $this->totalAgainstRejected;
    }

    /**
     * Set isActive
     *
     * @param string $isActive
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    }

    /**
     * Get isActive
     *
     * @return string $isActive
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set deleted
     *
     * @param string $deleted
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
    }

    /**
     * Get deleted
     *
     * @return string $deleted
     */
    public function getDeleted()
    {
        return $this->deleted;
    }
}
