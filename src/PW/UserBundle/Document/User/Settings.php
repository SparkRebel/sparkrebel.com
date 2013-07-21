<?php

namespace PW\UserBundle\Document\User;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use JMS\SerializerBundle\Annotation as API;
use PW\ApplicationBundle\Document\AbstractDocument;

/**
 * @MongoDB\EmbeddedDocument
 */
class Settings extends AbstractDocument
{
    /**
     * @var \PW\UserBundle\Document\User\Settings\Push
     * @MongoDB\EmbedOne(targetDocument="PW\UserBundle\Document\User\Settings\Push")
     * @API\Exclude
     */
    protected $push;

    /**
     * @var \PW\UserBundle\Document\User\Settings\Email
     * @MongoDB\EmbedOne(targetDocument="PW\UserBundle\Document\User\Settings\Email")
     * @API\Exclude
     */
    protected $email;

    /**
     * @var array
     * @MongoDB\Hash
     * @API\Exclude
     */
    protected $signupPreferences;

    /**
     * @var array
     * @MongoDB\Hash
     * @API\Exclude
     */
    protected $viewedGuiders;

    /**
     * @var \DateTime
     * @MongoDB\Date
     * @API\Exclude
     */
    protected $viewedTutorial;

    /**
     * By default what does this user want as their default value for post on Facebook.
     * Currently updates each time they post to whatever the user selected
     *
     * @var bool
     * @MongoDB\Boolean
     */
    protected $postOnFacebook = true;

    /**
     * @var bool
     * @MongoDB\NotSaved
     * @API\Exclude
     */
    protected $isActive;

    /**
     * @var \DateTime
     * @MongoDB\NotSaved
     * @API\Exclude
     */
    protected $deleted;

    /**
     * Get viewedGuiders
     *
     * @return array $viewedGuiders
     */
    public function getViewedGuiders()
    {
        if ($this->viewedGuiders === null) {
            $this->viewedGuiders = array(
                'respark' => null,
            );
        }
        return $this->viewedGuiders;
    }

    /**
     * @param string $id
     */
    public function addViewedGuider($id)
    {
        $guiders = $this->getViewedGuiders();
        $guiders[$id] = time();
        $this->setViewedGuiders($guiders);
    }

    /**
     * Get signupPreferences
     *
     * @return array $signupPreferences
     */
    public function getSignupPreferences()
    {
        if ($this->signupPreferences === null) {
            $this->signupPreferences = array();
        }
        return $this->signupPreferences;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $data = parent::toArray();
        unset($data['isActive']);
        unset($data['deleted']);
        return $data;
    }

    /**
     * Serialize data we want available in APP.me
     * "safe" because it is visible from JavaScript
     *
     * @return array
     */
    public function safeToArray()
    {
        $viewedTutorial    = $this->getViewedTutorial();
        $signupPreferences = $this->getSignupPreferences();

        return array(
            'viewed_guiders'     => $this->getViewedGuiders(),
            'viewed_tutorial'    => !empty($viewedTutorial)    ? $viewedTutorial    : false,
            'signup_preferences' => !empty($signupPreferences) ? $signupPreferences : false,
        );
    }

    /**
     * @return array
     */
    public function getAdminValue()
    {
        return $this->toArray();
    }

    /**
     * Get push
     *
     * @return \PW\UserBundle\Document\User\Settings\Push $push
     */
    public function getPush()
    {
        if (!$this->push) {
            $this->push = new \PW\UserBundle\Document\User\Settings\Push();
        }
        return $this->push;
    }

    /**
     * Get email
     *
     * @return \PW\UserBundle\Document\User\Settings\Email $email
     */
    public function getEmail()
    {
        if (!$this->email) {
            $this->email = new \PW\UserBundle\Document\User\Settings\Email();
        }
        return $this->email;
    }

    //
    // Doctrine Generation Below
    //

    /**
     * Set push
     *
     * @param PW\UserBundle\Document\User\Settings\Push $push
     */
    public function setPush(\PW\UserBundle\Document\User\Settings\Push $push)
    {
        $this->push = $push;
    }

    /**
     * Set email
     *
     * @param PW\UserBundle\Document\User\Settings\Email $email
     */
    public function setEmail(\PW\UserBundle\Document\User\Settings\Email $email)
    {
        $this->email = $email;
    }

    /**
     * Set signupPreferences
     *
     * @param hash $signupPreferences
     */
    public function setSignupPreferences($signupPreferences)
    {
        $this->signupPreferences = $signupPreferences;
    }

    /**
     * Set viewedGuiders
     *
     * @param hash $viewedGuiders
     */
    public function setViewedGuiders($viewedGuiders)
    {
        $this->viewedGuiders = $viewedGuiders;
    }

    /**
     * Set viewedTutorial
     *
     * @param date $viewedTutorial
     */
    public function setViewedTutorial($viewedTutorial)
    {
        $this->viewedTutorial = $viewedTutorial;
    }

    /**
     * Set postOnFacebook
     *
     * @param boolean $postOnFacebook
     */
    public function setPostOnFacebook($postOnFacebook)
    {
        $this->postOnFacebook = $postOnFacebook;
    }

    /**
     * Get postOnFacebook
     *
     * @return boolean $postOnFacebook
     */
    public function getPostOnFacebook()
    {
        return $this->postOnFacebook;
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

    /**
     * Get viewedTutorial
     *
     * @return date $viewedTutorial
     */
    public function getViewedTutorial()
    {
        return $this->viewedTutorial;
    }
    /**
     * @var date $created
     */
    protected $created;

    /**
     * @var date $modified
     */
    protected $modified;


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
