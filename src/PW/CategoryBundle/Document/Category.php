<?php

namespace PW\CategoryBundle\Document;

use PW\ApplicationBundle\Document\AbstractDocument,
    Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB,
    Gedmo\Mapping\Annotation as Gedmo,
    Symfony\Component\Validator\Constraints as Assert,
    JMS\SerializerBundle\Annotation as API;

/**
 * @MongoDB\Document(collection="categories", repositoryClass="PW\CategoryBundle\Repository\CategoryRepository")
 * @MongoDB\Indexes({
 *      @MongoDB\UniqueIndex(keys={"parent.$id"="asc", "name"="asc", "type"="asc"}, background=true)
 * })
 * @API\ExclusionPolicy("none")
 * @API\AccessType("public_method")
 */
class Category extends AbstractDocument
{
    /**
     * @MongoDB\Id
     * @API\Accessor(getter="getId", setter="setId")
     */
    protected $id;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Category")
     */
    protected $parent;

    /**
     * @MongoDB\Int
     * @API\Exclude
     */
    protected $weight = 0;

    /**
     * @MongoDB\String
     * @Assert\NotBlank(message="Category Name cannot be left blank.")
     */
    protected $name;

    /**
     * @var string
     * @Gedmo\Slug(fields={"name"})
     * @MongoDB\String
     */
    protected $slug;

    /**
     * @MongoDB\Boolean
     * @API\Exclude
     */
    protected $isActive = true;

    /**
     * @MongoDB\String
     * @Assert\Choice(choices = {"user", "item"}, message = "Choose a valid Category Type.")
     * @API\Exclude
     */
    protected $type = 'user';

    /**
     * @Gedmo\Timestampable(on="create")
     * @MongoDB\Date
     * @API\Exclude
     */
    protected $created;

    /**
     * @MongoDB\ReferenceOne(targetDocument="PW\UserBundle\Document\User")
     * @API\Exclude
     */
    protected $createdBy;

    /**
     * @Gedmo\Timestampable(on="update")
     * @MongoDB\Date
     * @API\Exclude
     */
    protected $modified;

    /**
     * @MongoDB\ReferenceOne(targetDocument="PW\UserBundle\Document\User")
     * @API\Exclude
     */
    protected $modifiedBy;

    /**
     * @MongoDB\Date
     * @API\Exclude
     */
    protected $deleted;

    /**
     * @MongoDB\ReferenceOne(targetDocument="PW\UserBundle\Document\User")
     * @API\Exclude
     */
    protected $deletedBy;


    /**
     * Use it for menu, then we can have more items separated
     * @MongoDB\Boolean
     * @API\Exclude
     */
    protected $isSeparated = false;

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * @return array
     */
    public function getAdminData()
    {
        $data = AbstractDocument::staticGetAdminData($this);
        $data['display'] = $this->getName();
        return $data;
    }

    /**
     * @return string
     */
    public function getAdminValue()
    {
        return $this->getName();
    }

    //
    // Doctrine Generation Below
    //

    /**
     * Get id
     *
     * @return id $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set created
     *
     * @param date $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * Get created
     *
     * @return date $created
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set createdBy
     *
     * @param PW\UserBundle\Document\User $createdBy
     */
    public function setCreatedBy(\PW\UserBundle\Document\User $createdBy)
    {
        $this->createdBy = $createdBy;
    }

    /**
     * Get createdBy
     *
     * @return PW\UserBundle\Document\User $createdBy
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set deleted
     *
     * @param date $deleted
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
    }

    /**
     * Get deleted
     *
     * @return date $deleted
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * Set deletedBy
     *
     * @param PW\UserBundle\Document\User $deletedBy
     */
    public function setDeletedBy(\PW\UserBundle\Document\User $deletedBy = null)
    {
        $this->deletedBy = $deletedBy;
    }

    /**
     * Get deletedBy
     *
     * @return PW\UserBundle\Document\User $deletedBy
     */
    public function getDeletedBy()
    {
        return $this->deletedBy;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    }

    /**
     * Get isActive
     *
     * @return boolean $isActive
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set parent
     *
     * @param PW\CategoryBundle\Document\Category $parent
     */
    public function setParent(\PW\CategoryBundle\Document\Category $parent)
    {
        $this->parent = $parent;
    }

    /**
     * Get parent
     *
     * @return PW\CategoryBundle\Document\Category $parent
     */
    public function getParent()
    {
        return $this->parent;
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

    /**
     * Set modifiedBy
     *
     * @param PW\UserBundle\Document\User $modifiedBy
     */
    public function setModifiedBy(\PW\UserBundle\Document\User $modifiedBy)
    {
        $this->modifiedBy = $modifiedBy;
    }

    /**
     * Get modifiedBy
     *
     * @return PW\UserBundle\Document\User $modifiedBy
     */
    public function getModifiedBy()
    {
        return $this->modifiedBy;
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set type
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return string $type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set slug
     *
     * @param string $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * Get slug
     *
     * @return string $slug
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set weight
     *
     * @param int $weight
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
    }

    /**
     * Get weight
     *
     * @return int $weight
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * getIsSeparated
     *
     * @return
     */
    public function getIsSeparated()
    {
        return $this->isSeparated;
    }

    /**
     * setIsSeparated
     *
     * @param mixed $isSeparated
     * @return Category
     */
    public function setIsSeparated($isSeparated)
    {
        $this->isSeparated = $isSeparated;
        return $this;
    }
    
    /**
     * getIsPromos - Is this "Sales & Promos" Category?
     *
     * @return
     */
    public function getIsPromos()
    {
        if ($this->getIsSeparated()) {
            return true;
        }
        $part_of_name = strtolower(substr($this->getName(),0,5));
        if (in_array($part_of_name, array('sales','promo'))) {
            return true;
        }
        return false;
    }

}
