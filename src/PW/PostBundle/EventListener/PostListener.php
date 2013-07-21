<?php

namespace PW\PostBundle\EventListener;

use PW\ApplicationBundle\EventListener\AbstractEventListener;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\SoftDelete\Event\LifecycleEventArgs as SoftDeleteEventArgs;
use PW\PostBundle\Events as PostEvents;
use PW\PostBundle\Event\PostEvent;
use PW\PostBundle\Document\Post;

class PostListener extends AbstractEventListener
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
        if ($document instanceOf Post) {
            // Increase User's Post/Repost count

            if ($createdBy = $document->getCreatedBy()) {
                $createdBy->getCounts()->incrementPosts();
                if ($document->getParent()) {
                    $createdBy->getCounts()->incrementReposts();
                }
                $this->getUserManager()->update($createdBy);
            }

            // Increase Parent's Repost count
            if ($parent = $document->getParent()) {
                $parent->incRepostCount();
                if ($original = $parent->getOriginal()) {
                    $original->incAggregateRepostCount();
                    $this->getPostManager()->save($original, array('validate' => false));
                } else {
                    $parent->incAggregateRepostCount();
                }
                $this->getPostManager()->save($parent, array('validate' => false));
            }

            // Increase Board's Post count
            if ($board = $document->getBoard()) {
                $board->incPostCount();
                $image = $document->getImage();
                if ($image) {
                    $board->addImages($image);
                }
                $this->getBoardManager()->save($board, array('validate' => false));
            }

            if ($document->getImage() && $document->getImage()->getIsVideo()) {
               $document->setIsVideoPost(true);
               $this->getPostManager()->save($document, array('validate' => false));
            }

            // Hide curators
            if ($document->getCreatedBy()->hasRole('ROLE_CURATOR')) {
               $this->getPostManager()->delete($document);
            }                        

            $this->getEventDispatcher()->dispatch(PostEvents::onNewPost, new PostEvent($document));
        }
    }

    /**
     * @param \Doctrine\ODM\MongoDB\SoftDelete\Event\LifecycleEventArgs $args
     */
    public function postSoftDelete(SoftDeleteEventArgs $args)
    {
        $document = $args->getDocument();

        if ($document instanceOf Post) {
            // Decrease User's Post/Repost count
            if ($createdBy = $document->getCreatedBy()) {
                $createdBy->getCounts()->decrementPosts();
                if ($document->getParent()) {
                    $createdBy->getCounts()->decrementReposts();
                }
                $this->getUserManager()->update($createdBy);
            }

            // Decrease Parent's Repost count
            if ($parent = $document->getParent()) {
                $parent->decrementRepostCount();
                if ($original = $parent->getOriginal()) {
                    $original->decrementAggregateRepostCount();
                    $this->getPostManager()->save($original, array('validate' => false));
                } else {
                    $parent->decrementAggregateRepostCount();
                }
                $this->getPostManager()->save($parent, array('validate' => false));
            }

            // Decrease Board's Post count and remove image from board
            if ($board = $document->getBoard()) {

                //$board->decrementPostCount();
                $this->getBoardManager()->processCounts($board);
                $board->removeImage($document->getImage());

                $this->getBoardManager()->save($board, array('validate' => false));
            }

            $this->getEventDispatcher()->dispatch(PostEvents::onDeletePost, new PostEvent($document));
        }
    }

    /**
      * @param \Doctrine\ODM\MongoDB\SoftDelete\Event\LifecycleEventArgs $args
      */
     public function preSoftDelete(SoftDeleteEventArgs $args)
     {
         $document = $args->getDocument();
         if ($document instanceOf Post) {
             $sdm = $args->getSoftDeleteManager();
             // After talking to liat - we cant delete all related reposts
         }
     }

}
