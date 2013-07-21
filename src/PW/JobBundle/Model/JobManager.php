<?php

namespace PW\JobBundle\Model;

use PW\ApplicationBundle\Model\AbstractManager,
    PW\JobBundle\Document\Job,
    PW\UserBundle\Document\User;

class JobManager extends AbstractManager
{
    protected $boardManager = null;
    
    /**
     * @param array $data
     * @return \PW\JobBundle\Document\Job
     */
    public function create(array $data = array())
    {
        /* @var $job \PW\JobBundle\Document\Job */
        $job = parent::create($data);
        $job->setCmd('getty:query "{keywords}" "{collection}" --env=prod');
        $job->setStartDate(new \DateTime());
        $job->setEndDate(new \DateTime());  
        return $job;
    }
    
    /**
     * @param \PW\JobBundle\Document\Job $job
     * @param string $newBoardName
     * @return \PW\JobBundle\Document\Job
     */
    public function createBoardForJob($job, $newBoardName)
    {
        $creator = $job->getUser();
        // check if Board of that creator already exists
        $qb = $this->boardManager->getRepository()
            ->createQueryBuilder()
            ->field('name')->equals($newBoardName)
            ->field('createdBy')->references($creator);
        $board = $qb->getQuery()->getSingleResult();
        if (!$board) {
            // create new Board
            $board = $this->boardManager->create(array('createdBy' => $creator));
            $board->setName($newBoardName);
            $board->setIsActive(false); //make hidden board in the beginning
            $this->dm->persist($board);
        }
        $job->setBoard($board);  
        return $job;
    }
    
    /**
     * @return mixed
     */
    public function findAllActive()
    {
        return $this->getRepository()->findAllActive()->getQuery()->execute();
    }
    
    /**
     * @return mixed
     */    
    public function findAllActiveAndRunning()
    {
        return $this->getRepository()->findAllActiveAndRunning()->getQuery()->execute();
    }
    
    
    /**
     * @param PW\UserBundle\Document\User $user
     * @return mixed
     */
    public function findByUser(User $user) {
        return $this->getRepository()->findByUser($user)->getQuery()->execute();
    }
    
    /**
     * @param \PW\BoardBundle\Model\BoardManager $boardManager
     */
    public function setBoardManager($boardManager) {
        $this->boardManager = $boardManager;
    }
}
