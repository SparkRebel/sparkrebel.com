<?php

namespace PW\InviteBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert,
    PW\InviteBundle\Document\Request as InviteRequest;

class CreateInviteRequest
{
    /**
     * @Assert\Type(type="PW\InviteBundle\Document\Request")
     * @Assert\Valid
     */
    protected $inviteRequest;

    /**
     * @param Request $inviteRequest
     */
    public function __construct(InviteRequest $inviteRequest = null)
    {
        $this->inviteRequest = $inviteRequest;
    }

    /**
     * @param Request $inviteRequest
     */
    public function setInviteRequest(InviteRequest $inviteRequest)
    {
        $this->inviteRequest = $inviteRequest;
    }

    /**
     * @return \PW\InviteBundle\Document\Request
     */
    public function getInviteRequest()
    {
        return $this->inviteRequest;
    }
}
