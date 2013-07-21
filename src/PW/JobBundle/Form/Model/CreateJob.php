<?php

namespace PW\JobBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert,
    PW\JobBundle\Document\Job;

class CreateJob
{
    /**
     * @Assert\Type(type="PW\JobBundle\Document\Job")
     * @Assert\Valid
     */
    protected $job;

    /**
     * @param Job $job
     */
    public function __construct(Job $job = null)
    {
        $this->job = $job;
    }

    /**
     * @param Job $job
     */
    public function setJob(Job $job)
    {
        $this->job = $job;
    }

    /**
     * @return \PW\JobBundle\Document\Job
     */
    public function getJob()
    {
        return $this->job;
    }
}
