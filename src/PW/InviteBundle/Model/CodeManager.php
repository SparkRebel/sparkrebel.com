<?php

namespace PW\InviteBundle\Model;

use PW\ApplicationBundle\Model\AbstractManager,
    PW\InviteBundle\Document\Code,
    PW\UserBundle\Document\User;

/**
 * @method \PW\InviteBundle\Repository\CodeRepository getRepository() getRepository()
 */
class CodeManager extends AbstractManager
{
    /**
     * @var array
     */
    protected $flushOptions = array(
        'safe'  => false,
        'fsync' => false,
    );

    /**
     * @param array $data
     * @return \PW\InviteBundle\Document\Code
     */
    public function create(array $data = array())
    {
        /* @var $code \PW\InviteBundle\Document\Code */
        $code = parent::create($data);
        if ($code->getType() == 'random') {
            $code->setValue($code->generateCode());
        } elseif (!$code->getType()) {
            $code->setType('custom');
        }
        if ($code->getMaxUses()) {
            $code->setUsesLeft($code->getMaxUses());
        }
        return $code;
    }

    /**
     * @param int $maxUses
     * @param User $createdBy
     * @return \PW\InviteBundle\Document\Code
     */
    public function createRandom($maxUses = 0, User $createdBy = null)
    {
        return $this->create(array(
            'maxUses'   => (int) $maxUses,
            'createdBy' => $createdBy,
            'type'      => 'random'
        ));
    }

    /**
     * @param int $total
     * @param int $maxUses
     * @param User $createdBy
     * @return array|\PW\InviteBundle\Document\Code
     */
    public function generate($total, $maxUses = 0, User $createdBy = null)
    {
        $codes   = array();
        $total   = (int) $total;
        $maxUses = (int) $maxUses;

        for ($i = 1; $i <= $total; $i++) {
            $code = $this->createRandom($maxUses, $createdBy);
            $this->update($code, false);
            $codes[] = $code;
        }

        $this->dm->flush();
        return $total == 1 ? reset($codes) : $codes;
    }

    /**
     * @param string $value
     * @return \PW\InviteBundle\Document\Code
     */
    public function findByValue($value)
    {
        return $this->getRepository()->findOneByValue($value);
    }
}
