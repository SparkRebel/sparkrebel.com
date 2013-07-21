<?php

namespace PW\UserBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert,
    PW\UserBundle\Document\User;

class CreateUser
{
    /**
     * @Assert\Type(type="PW\UserBundle\Document\User")
     * @Assert\Valid
     */
    protected $user;

    /**
     * @var string
     */
    protected $type;

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
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
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
