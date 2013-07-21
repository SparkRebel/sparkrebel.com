<?php

namespace PW\UserBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;
use PW\UserBundle\Document\User;

class EditUser
{
    /**
     * @Assert\Type(type="PW\UserBundle\Document\User")
     * @Assert\Valid
     */
    protected $user;

    /**
     * @var bool
     */
    protected $deleteIcon;

    /**
     * @var \Symfony\Component\HttpFoundation\File\UploadedFile
     */
    protected $newIcon;

    /**
     * @param User $user
     */
    public function __construct(User $user = null)
    {
        $this->user = $user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return \PW\UserBundle\Document\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param bool $deleteIcon
     */
    public function setDeleteIcon($deleteIcon)
    {
        $this->deleteIcon = $deleteIcon;
    }

    /**
     * @return bool
     */
    public function getDeleteIcon()
    {
        return $this->deleteIcon;
    }

    /**
     * @param bool $newIcon
     */
    public function setNewIcon($newIcon)
    {
        $this->newIcon = $newIcon;
    }

    /**
     * @return bool
     */
    public function getNewIcon()
    {
        return $this->newIcon;
    }
}
