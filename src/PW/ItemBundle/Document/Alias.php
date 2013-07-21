<?php

namespace PW\ItemBundle\Document;

use PW\ApplicationBundle\Document\AbstractDocument,
    Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB,
    Gedmo\Mapping\Annotation as Gedmo;

/**
 * Canonical names
 *
 * Stores a list of synonyms for brand and store names
 *
 * @MongoDB\Document(collection="aliases")
 */
class Alias extends AbstractDocument
{
    /**
     * @MongoDB\Id(strategy="NONE")
     */
    protected $id;

    /**
     * @MongoDB\Collection
     */
    protected $synonyms;

    /**
     * Set id
     *
     * @param custom_id $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get id
     *
     * @return custom_id $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set synonyms
     *
     * @param collection $synonyms
     */
    public function setSynonyms($synonyms)
    {
        $this->synonyms = $synonyms;
    }

    /**
     * Get synonyms
     *
     * @return collection $synonyms
     */
    public function getSynonyms()
    {
        return $this->synonyms;
    }
}
