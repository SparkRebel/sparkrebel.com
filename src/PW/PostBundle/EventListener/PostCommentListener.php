<?php

namespace PW\PostBundle\EventListener;

use PW\ApplicationBundle\EventListener\AbstractEventListener;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\SoftDelete\Event\LifecycleEventArgs as SoftDeleteEventArgs;
use PW\PostBundle\Events as PostEvents;
use PW\PostBundle\Event\PostCommentEvent;
use PW\PostBundle\Document\PostComment;

class PostCommentListener extends AbstractEventListener
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
        if ($document instanceOf PostComment) {
            // Increase Post's comment count
            if ($post = $document->getPost()) {
                $post->incCommentCount();
                $this->getPostManager()->save($post, array('validate' => false));
            }

            $this->getEventDispatcher()->dispatch(PostEvents::onNewComment, new PostCommentEvent($document));
        }
    }

    /**
     * @param \Doctrine\ODM\MongoDB\SoftDelete\Event\LifecycleEventArgs $args
     */
    public function postSoftDelete(SoftDeleteEventArgs $args)
    {
        $document = $args->getDocument();
        if ($document instanceOf PostComment) {
            // Decrease Post's comment count
            if ($post = $document->getPost()) {
                $this->getPostManager()->processCounts($post);
            }

            $this->getEventDispatcher()->dispatch(PostEvents::onDeleteComment, new PostCommentEvent($document));
        }
    }
}