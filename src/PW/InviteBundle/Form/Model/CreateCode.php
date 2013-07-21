<?php

namespace PW\InviteBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert,
    PW\InviteBundle\Document\Code;

class CreateCode
{
    /**
     * @Assert\Type(type="PW\InviteBundle\Document\Code")
     * @Assert\Valid
     */
    protected $code;

    /**
     * @param Code $inviteCode
     */
    public function __construct(Code $code = null)
    {
        $this->code = $code;
    }

    /**
     * @param Code $inviteCode
     */
    public function setCode(Code $code)
    {
        $this->code = $code;
    }

    /**
     * @return \PW\InviteBundle\Document\Code
     */
    public function getCode()
    {
        return $this->code;
    }
}
