<?php

namespace PW\InviteBundle\Model;

use PW\ApplicationBundle\Model\AbstractManager,
    PW\InviteBundle\Document\Request,
    PW\InviteBundle\Document\Code,
    PW\UserBundle\Document\User;

/**
 * @method \PW\InviteBundle\Document\Request create()
 * @method \PW\InviteBundle\Document\Request update(Request $code, bool $andFlush)
 */
class RequestManager extends AbstractManager
{
    /**
     * @var array
     */
    protected $flushOptions = array(
        'safe'  => false,
        'fsync' => false,
    );

    /**
     * @param Request $request
     * @param Code $code
     * @param User $assignedBy
     * @return \PW\InviteBundle\Document\Request
     */
    public function assignCode(Request $request, Code $code, User $assignedBy = null)
    {
        $request->setCode($code);
        $request->setAssignedCodeAt(new \DateTime());
        if ($assignedBy !== null) {
            $request->setAssignedCodeBy($assignedBy);
        }
        $this->update($request);
        return $request;
    }
}
