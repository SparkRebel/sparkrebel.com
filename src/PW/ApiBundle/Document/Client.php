<?php

namespace PW\ApiBundle\Document;

use FOS\OAuthServerBundle\Model\Client as BaseClient;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ODM\MongoDB\SoftDelete\SoftDeleteable;
use OAuth2\OAuth2;

/**
 * @MongoDB\Document(collection="api_clients", repositoryClass="PW\ApiBundle\Repository\ClientRepository")
 */
class Client extends BaseClient implements SoftDeleteable
{
    /**
     * @var \MongoId
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @var string
     * @MongoDB\String
     */
    protected $randomId;

    /**
     * @var string
     * @MongoDB\String
     */
    protected $name;

    /**
     * @var \PW\UserBundle\Document\User
     * @MongoDB\ReferenceOne(targetDocument="PW\UserBundle\Document\User", simple=true)
     */
    protected $user;

    /**
     * @var array
     * @MongoDB\Hash
     */
    protected $redirectUris = array();

    /**
     * @var string
     * @MongoDB\String
     */
    protected $secret;

    /**
     * @var array
     * @MongoDB\Hash
     */
    protected $allowedGrantTypes = array();

    /**
     * @var bool
     * @MongoDB\Boolean
     */
    protected $isActive = true;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @MongoDB\Date
     */
    protected $created;

    /**
     * @var \PW\UserBundle\Document\User
     * @MongoDB\ReferenceOne(targetDocument="PW\UserBundle\Document\User", simple=true)
     */
    protected $createdBy;

    /**
     * @var \DateTimem
     * @Gedmo\Timestampable(on="update")
     * @MongoDB\Date
     */
    protected $modified;

    /**
     * @var \PW\UserBundle\Document\User
     * @MongoDB\ReferenceOne(targetDocument="PW\UserBundle\Document\User", simple=true)
     */
    protected $modifiedBy;

    /**
     * @var \DateTime
     * @MongoDB\Date
     */
    protected $deleted;

    /**
     * @var \PW\UserBundle\Document\User
     * @MongoDB\ReferenceOne(targetDocument="PW\UserBundle\Document\User", simple=true)
     */
    protected $deletedBy;

    /**
     * Set redirectUris
     *
     * @param array $redirectUris
     */
    public function setRedirectUris(array $redirectUris)
    {
        $this->redirectUris = $redirectUris;
    }

    /**
     * Set allowedGrantTypes
     *
     * @param array $allowedGrantTypes
     */
    public function setAllowedGrantTypes(array $allowedGrantTypes)
    {
        $this->allowedGrantTypes = $allowedGrantTypes;
    }

    /**
     * Required by SoftDeleteable Interface
     * @return \DateTime
     */
    public function getDeletedAt()
    {
        return $this->deleted;
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
     * Set randomId
     *
     * @param string $randomId
     */
    public function setRandomId($randomId)
    {
        $this->randomId = $randomId;
    }

    /**
     * Get randomId
     *
     * @return string $randomId
     */
    public function getRandomId()
    {
        return $this->randomId;
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
     * Set user
     *
     * @param PW\UserBundle\Document\User $user
     */
    public function setUser(\PW\UserBundle\Document\User $user)
    {
        $this->user = $user;
    }

    /**
     * Get user
     *
     * @return PW\UserBundle\Document\User $user
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Get redirectUris
     *
     * @return hash $redirectUris
     */
    public function getRedirectUris()
    {
        return $this->redirectUris;
    }

    /**
     * Set secret
     *
     * @param string $secret
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;
    }

    /**
     * Get secret
     *
     * @return string $secret
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * Get allowedGrantTypes
     *
     * @return hash $allowedGrantTypes
     */
    public function getAllowedGrantTypes()
    {
        return $this->allowedGrantTypes;
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
}
