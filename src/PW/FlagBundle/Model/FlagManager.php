<?php

namespace PW\FlagBundle\Model;

use PW\ApplicationBundle\Model\AbstractManager,
    PW\ApplicationBundle\Model\EventManager,
    PW\FlagBundle\Document\Flag,
    PW\UserBundle\Document\User,
    PW\PostBundle\Document\Post,
    PW\PostBundle\Document\PostActivity;

class FlagManager extends AbstractManager
{
    /**
     * @var array
     */
    protected $flushOptions = array(
        'safe'  => false,
        'fsync' => false,
    );

    /**
     * @var \PW\PostBundle\Model\PostManager
     */
    protected $postManager;

    /**
     * @var \PW\ApplicationBundle\Model\EventManager
     */
    protected $eventManager;

    /**
     * @param array $data
     * @return \PW\FlagBundle\Document\Flag
     */
    public function create(array $data = array())
    {
        /* @var $flag \PW\FlagBundle\Document\Flag */
        $flag = parent::create($data);
        if ($flag->getCreatedBy()) {
            $flag->getCreatedBy()->getFlagSummary()->incTotalBy();
        }
        return $flag;
    }

    /**
     * @param mixed $object
     * @param string $reason
     * @param User $flaggedBy
     * @return \PW\FlagBundle\Document\Flag
     */
    public function flagObject($object, $reason, User $flaggedBy = null)
    {
        $flag = $this->create(array(
            'createdBy'  => $flaggedBy,
            'target'     => $object,
            'targetUser' => $object->getCreatedBy(),
            'reason'     => $reason,
        ));
        $this->update($flag);
        return $flag;
    }

    /**
     * @param Flag $flag
     * @param User $approvedBy
     * @return \PW\FlagBundle\Document\Flag
     * @throws \Exception
     */
    public function approve(Flag $flag, User $approvedBy = null)
    {
        $flag->approve($approvedBy);
        $target = $flag->getTarget();

        if ($target instanceOf Post) {
            $this->eventManager->publish('asset.remove', array(
                'assetId' => $target->getImage()->getId()
            ));
        } elseif ($target instanceOf PostActivity) {
            $this->delete($target, $approvedBy);
        } else {
            throw new \Exception(sprintf("Cannot approve flag object of type '%'", get_class($target)));
        }

        $this->update($flag);
        return $flag;
    }

    /**
     * @param Flag $flag
     * @param User $rejectedBy
     * @return \PW\FlagBundle\Document\Flag
     */
    public function reject(Flag $flag, User $rejectedBy = null)
    {
        $flag->reject($rejectedBy);
        $this->update($flag);
        return $flag;
    }

    /**
     * @param \PW\ApplicationBundle\Model\EventManager $eventManager
     */
    public function setEventManager(EventManager $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    /**
     * @param \PW\PostBundle\Model\PostManager $postManager
     */
    public function setPostManager($postManager)
    {
        $this->postManager = $postManager;
    }
}
