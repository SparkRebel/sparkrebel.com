<?php

namespace PW\SearchBundle\Provider;

use Doctrine\ODM\MongoDB\Query\Builder;
use FOQ\ElasticaBundle\Exception\InvalidArgumentTypeException;
use FOQ\ElasticaBundle\Persister\ObjectPersister;

class Provider
{
    /**
     * @var \FOQ\ElasticaBundle\Persister\ObjectPersister
     */
    protected $objectPersister;

    /**
     * @var
     */
    protected $objectManager;

    /**
     * @var
     */
    protected $repoMethod;

    /**
     * @var
     */
    protected $lockers;

    public function __construct(ObjectPersister $objectPersister, $objectManager, $repoMethod)
    {
        $this->objectPersister = $objectPersister;
        $this->objectManager = $objectManager;
        $this->repoMethod = $repoMethod;

        $this->unserializeLockers();
    }

    /**
     * Persists all domain objects to ElasticSearch for this provider.
     *
     * @param $start the start date from where to fetch data
     * @param Closure $loggerClosure
     * @param $lockerId the locker id
     */
    function populate($start, \Closure $loggerClosure = null, $lockerId)
    {
        if($this->isProcessLocked($lockerId) && $loggerClosure) {
            $loggerClosure('<info>Another process is running for this index type!</info>');

            return;
        }

        $this->lockProcess($lockerId);

        $startOffset = $this->getLockerParameter($lockerId, 'offset', 0);
        $startTimestamp = $this->getLockerParameter($lockerId, 'startTimestamp', $start->getTimestamp());

        $queryBuilder = $this->createQueryBuilder($startTimestamp);
        $nbObjects = $this->countObjects($queryBuilder);

        for ($offset = $startOffset; $offset < $nbObjects; $offset += 100) {
            if ($loggerClosure) {
                $stepStartTime = microtime(true);
            }

            try {
                $objects = $this->fetchSlice($queryBuilder, 100, $offset);

                foreach($objects as $object) {
                    $this->objectPersister->replaceOne($object);
                }

                if ($loggerClosure) {
                    $stepNbObjects = count($objects);
                    $stepCount = $stepNbObjects + $offset;
                    $percentComplete = 100 * $stepCount / $nbObjects;
                    $objectsPerSecond = $stepNbObjects / (microtime(true) - $stepStartTime);
                    $loggerClosure(sprintf('%0.1f%% (%d/%d), %d objects/s', $percentComplete, $stepCount, $nbObjects, $objectsPerSecond));

                }

                $this->lockRepopulation($lockerId, $stepCount, $startTimestamp);

                /**
                 * if less than 40 objects per second are replaced, then stop repopulation
                 * so next time command is run, last offset is used instead of going from 0
                 */
                if($objectsPerSecond < 40) {
                    break;
                }
            }
            catch (\Exception $e)
            {
                $loggerClosure('<info>Some error appeared: '.$e->getMessage().'</info>');
                break;
            }
        }

        if($offset >= $nbObjects) {
            $loggerClosure('<info>Repopulation is already finished!</info>');
        }

        $this->unlockProcess($lockerId);
    }

    protected function lockRepopulation($lockerId, $offset, $startTimestamp)
    {
        $this->lockers[$lockerId]['offset'] = $offset;
        $this->lockers[$lockerId]['startTimestamp'] = $startTimestamp;

        $this->serializeLockers();
    }

    protected function lockProcess($lockerId)
    {
        $this->lockers[$lockerId]['locked'] = true;

        $this->serializeLockers();
    }

    protected function unlockProcess($lockerId)
    {
        $this->lockers[$lockerId]['locked'] = false;

        $this->serializeLockers();
    }

    protected function isProcessLocked($lockerId)
    {
        return $this->getLockerParameter($lockerId, 'locked', false);
    }

    protected function hasLockerParameter($lockerId, $parameter)
    {
        return (isset($this->lockers[$lockerId]) && isset($this->lockers[$lockerId][$parameter]));
    }

    protected function getLockerParameter($lockerId, $parameter, $defaultValue)
    {
        return $this->hasLockerParameter($lockerId, $parameter) ? $this->lockers[$lockerId][$parameter] : $defaultValue;
    }

    public function getLockersFilePath()
    {
        return sys_get_temp_dir().'/elastic_lockers';
    }

    protected function serializeLockers()
    {
        file_put_contents($this->getLockersFilePath(),serialize($this->lockers));
    }

    protected function unserializeLockers()
    {
        $this->lockers = file_exists($this->getLockersFilePath()) ? unserialize(file_get_contents($this->getLockersFilePath())) : array();
    }

    public function removeLocker($lockerId)
    {
        unset($this->lockers[$lockerId]);

        $this->serializeLockers();
    }

    /**
     * @see FOQ\ElasticaBundle\Doctrine\AbstractProvider::countObjects()
     */
    protected function countObjects($queryBuilder)
    {
        if (!$queryBuilder instanceof Builder) {
            throw new InvalidArgumentTypeException($queryBuilder, 'Doctrine\ODM\MongoDB\Query\Builder');
        }

        return $queryBuilder
            ->getQuery()
            ->count();
    }

    /**
     * @see FOQ\ElasticaBundle\Doctrine\AbstractProvider::fetchSlice()
     */
    protected function fetchSlice($queryBuilder, $limit, $offset)
    {
        if (!$queryBuilder instanceof Builder) {
            throw new InvalidArgumentTypeException($queryBuilder, 'Doctrine\ODM\MongoDB\Query\Builder');
        }

        return $queryBuilder
            ->limit($limit)
            ->skip($offset)
            ->getQuery()
            ->execute()
            ->toArray();
    }

    /**
     * @see FOQ\ElasticaBundle\Doctrine\AbstractProvider::createQueryBuilder()
     * @param $startTimestamp the start timestamp from where to fetch data
     */
    protected function createQueryBuilder($startTimestamp)
    {
        return $this->objectManager
            ->getRepository()
            ->{$this->repoMethod}($startTimestamp);
    }
}
