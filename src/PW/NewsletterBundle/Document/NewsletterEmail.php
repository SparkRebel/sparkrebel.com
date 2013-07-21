<?php

/**
 * @author Radu Topala <radu@sparkrebel.com>
 */

namespace PW\NewsletterBundle\Document;

use PW\ApplicationBundle\Document\AbstractDocument,
    Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB,
    Symfony\Component\Validator\Constraints as Assert,
    Gedmo\Mapping\Annotation as Gedmo,
    PW\AssetBundle\Document\Asset;

/**
 * NewsletterEmail
 *
 * @MongoDB\Document(collection="newsletter_emails", repositoryClass="PW\NewsletterBundle\Repository\NewsletterEmailRepository")
 * @MongoDB\Indexes({
 *      @MongoDB\Index(keys={"newsletter.$id"="asc"}, background=true),
 * })
 */
class NewsletterEmail extends AbstractDocument
{
    /**
    * @MongoDB\Id
    */
    protected $id;

    /**
     * @var string
     * @MongoDB\String
     */
    protected $code;

    /**
     * @var \PW\NewsletterBundle\Document\Newsletter
     * @MongoDB\ReferenceOne(targetDocument="PW\NewsletterBundle\Document\Newsletter")
     */
    protected $newsletter;

    /**
     * @MongoDB\String
     */
    protected $content;

    /**
     * @Gedmo\Timestampable(on="create")
     * @MongoDB\Date
     */
    protected $created;

    /**
     * @Gedmo\Timestampable(on="update")
     * @MongoDB\Date
     */
    protected $modified;

    /**
     * @MongoDB\Date
     */
    protected $sentAt;

    /**
     * @MongoDB\ReferenceOne(targetDocument="PW\UserBundle\Document\User")
     */
    protected $user;


    /**
     * @var boolean $isActive
     */
    protected $isActive;

    /**
     * @var date $deleted
     */
    protected $deleted;


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
     * Set code
     *
     * @param string $code
     * @return NewsletterEmail
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * Get code
     *
     * @return string $code
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set newsletter
     *
     * @param PW\NewsletterBundle\Document\Newsletter $newsletter
     * @return NewsletterEmail
     */
    public function setNewsletter(\PW\NewsletterBundle\Document\Newsletter $newsletter)
    {
        $this->newsletter = $newsletter;
        return $this;
    }

    /**
     * Get newsletter
     *
     * @return PW\NewsletterBundle\Document\Newsletter $newsletter
     */
    public function getNewsletter()
    {
        return $this->newsletter;
    }

    /**
     * Set content
     *
     * @param string $content
     * @return NewsletterEmail
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Get content
     *
     * @return string $content
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set created
     *
     * @param date $created
     * @return NewsletterEmail
     */
    public function setCreated($created)
    {
        $this->created = $created;
        return $this;
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
     * Set modified
     *
     * @param date $modified
     * @return NewsletterEmail
     */
    public function setModified($modified)
    {
        $this->modified = $modified;
        return $this;
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
     * Set sentAt
     *
     * @param date $sentAt
     * @return NewsletterEmail
     */
    public function setSentAt($sentAt)
    {
        $this->sentAt = $sentAt;
        return $this;
    }

    /**
     * Get sentAt
     *
     * @return date $sentAt
     */
    public function getSentAt()
    {
        return $this->sentAt;
    }

    /**
     * Set user
     *
     * @param PW\UserBundle\Document\User $user
     * @return NewsletterEmail
     */
    public function setUser(\PW\UserBundle\Document\User $user)
    {
        $this->user = $user;
        return $this;
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
     * Set isActive
     *
     * @param boolean $isActive
     * @return NewsletterEmail
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
        return $this;
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
     * Set deleted
     *
     * @param date $deleted
     * @return NewsletterEmail
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
        return $this;
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
}
