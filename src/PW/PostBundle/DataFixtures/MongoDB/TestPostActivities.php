<?php

namespace PW\PostBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\AbstractFixture,
    Doctrine\Common\DataFixtures\DependentFixtureInterface,
    Doctrine\Common\Persistence\ObjectManager,
    Doctrine\ODM\MongoDB\DocumentManager,
    PW\PostBundle\Document\PostComment;

class TestPostActivities extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * @return array
     */
    public function getDependencies()
    {
        return array(
            'PW\UserBundle\DataFixtures\MongoDB\TestUsers',
            'PW\PostBundle\DataFixtures\MongoDB\TestPosts',
        );
    }

    /**
     * @param \Doctrine\ODM\MongoDB\DocumentManager $dm
     */
    public function load(ObjectManager $dm)
    {
        // For each User...
        for ($userCount = 1; $userCount <= $GLOBALS['FIXTURE_USERS_TOTAL']; $userCount++) {
            if ($this->hasReference("user-{$userCount}")) {

                // ... and for each User's Board...
                for ($boardCount = 1; $boardCount <= $GLOBALS['FIXTURE_BOARDS_TOTAL']; $boardCount++) {
                    if ($this->hasReference("user-{$userCount}-board-{$boardCount}")) {

                        // ... and for each Board's Post...
                        for ($postCount = 1; $postCount <= $GLOBALS['FIXTURE_POSTS_TOTAL']; $postCount++) {
                            if ($this->hasReference("user-{$userCount}-board-{$boardCount}-post-{$postCount}")) {

                                // ... create 1 Comment
                                $nextUser = $userCount + 1;
                                if (!$this->hasReference("user-{$nextUser}")) {
                                    $nextUser = 1;
                                }

                                $post = $this->getReference("user-{$userCount}-board-{$boardCount}-post-{$postCount}");

                                $comment = new PostComment();
                                $comment->setCreatedBy($this->getReference("user-{$nextUser}"));
                                $comment->setContent("User #{$nextUser} - Board #{$boardCount} - Post #{$postCount} - Comment");
                                $comment->setPost($post);
                                $dm->persist($comment);

                                $post->addActivity($comment);
                                $dm->persist($post);
                                $dm->flush();

                                // ... and 1 Comment reply
                                $reply = new PostComment();
                                $reply->setCreatedBy($this->getReference("user-{$userCount}"));
                                $reply->setContent("User #{$userCount} - Board #{$boardCount} - Post #{$postCount} - Comment Reply");
                                $reply->setPost($post);
                                $dm->persist($reply);

                                $comment->addSubactivity($reply);
                                $dm->flush();

                                // ... and 1 Comment for the Re-Post
                                $post = $this->getReference("user-{$userCount}-board-{$boardCount}-repost-{$postCount}");

                                $comment = new PostComment();
                                $comment->setCreatedBy($this->getReference("user-{$nextUser}"));
                                $comment->setContent("User #{$nextUser} - Board #{$boardCount} - Post #{$postCount} - Re-Post - Comment");
                                $comment->setPost($post);
                                $dm->persist($comment);

                                $post->addActivity($comment);
                                $dm->persist($post);
                                $dm->flush();

                                // ... and 1 Comment reply
                                $reply = new PostComment();
                                $reply->setCreatedBy($this->getReference("user-{$userCount}"));
                                $reply->setContent("User #{$userCount} - Board #{$boardCount} - Post #{$postCount} - Re-Post - Comment Reply");
                                $reply->setPost($post);
                                $dm->persist($reply);

                                $comment->addSubactivity($reply);
                                $dm->flush();

                            }
                        }
                    }
                }
            }
        }
    }
}
