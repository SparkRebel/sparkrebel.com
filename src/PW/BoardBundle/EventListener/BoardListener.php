<?php

namespace PW\BoardBundle\EventListener;

use PW\ApplicationBundle\EventListener\AbstractEventListener;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\SoftDelete\Event\LifecycleEventArgs as SoftDeleteEventArgs;
use PW\BoardBundle\Events as BoardEvents;
use PW\BoardBundle\Event\BoardEvent;
use PW\BoardBundle\Document\Board;

class BoardListener extends AbstractEventListener
{
    /**
     * The postPersist event occurs for an document after the document has been made persistent.
     * It will be invoked after the database insert operations.
     * Generated primary key values are available in the postPersist event.
     *
     * @param \Doctrine\ODM\MongoDB\Event\LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $document = $args->getDocument();
        if ($document instanceOf Board) {
            // Increase User's Board count
            if ($createdBy = $document->getCreatedBy()) {
                $createdBy->getCounts()->incrementBoards();
                $this->getUserManager()->update($createdBy);
            }

            $this->getEventDispatcher()->dispatch(BoardEvents::onNewBoard, new BoardEvent($document));
        }
    }

    /**
     * @param \Doctrine\ODM\MongoDB\SoftDelete\Event\LifecycleEventArgs $args
     */
    public function preSoftDelete(SoftDeleteEventArgs $args)
    {
        $document = $args->getDocument();
        if ($document instanceOf Board) {
            $sdm = $args->getSoftDeleteManager();
            $sdm->deleteBy('PW\PostBundle\Document\Post', array('board.$id' => $document->getId()));
            $sdm->deleteBy('PW\UserBundle\Document\Follow', array('target.$id' => $document->getId()));
        }
    }

    /**
     * @param \Doctrine\ODM\MongoDB\SoftDelete\Event\LifecycleEventArgs $args
     */
    public function postSoftDelete(SoftDeleteEventArgs $args)
    {
        $document = $args->getDocument();
        if ($document instanceOf Board) {
            // Decrease User's Board count
            if ($createdBy = $document->getCreatedBy()) {
                $createdBy->getCounts()->decrementBoards();
                $this->getUserManager()->update($createdBy);
            }

            $this->getEventDispatcher()->dispatch(BoardEvents::onDeleteBoard, new BoardEvent($document));
        }
    }
}
