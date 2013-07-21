<?php

namespace PW\PostBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\AbstractFixture,
    Doctrine\Common\DataFixtures\DependentFixtureInterface,
    Doctrine\Common\Persistence\ObjectManager,
    Doctrine\ODM\MongoDB\DocumentManager,
    PW\PostBundle\Document\Post;

class TestPosts extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * @return array
     */
    public function getDependencies()
    {
        return array(
            'PW\UserBundle\DataFixtures\MongoDB\TestUsers',
            'PW\BoardBundle\DataFixtures\MongoDB\TestBoards',
        );
    }

    /**
     * @param \Doctrine\ODM\MongoDB\DocumentManager $dm
     */
    public function load(ObjectManager $dm)
    {
        if (!isset($GLOBALS['FIXTURE_POSTS_TOTAL'])) {
            $GLOBALS['FIXTURE_POSTS_TOTAL'] = 2;
        }

        // For each User...
        for ($userCount = 1; $userCount <= $GLOBALS['FIXTURE_USERS_TOTAL']; $userCount++) {
            if ($this->hasReference("user-{$userCount}")) {

                // ... and for each User's Board...
                for ($boardCount = 1; $boardCount <= $GLOBALS['FIXTURE_BOARDS_TOTAL']; $boardCount++) {
                    if ($this->hasReference("user-{$userCount}-board-{$boardCount}")) {

                        // ... create 2 Posts
                        for ($postCount = 1; $postCount <= $GLOBALS['FIXTURE_POSTS_TOTAL']; $postCount++) {
                            $post = new Post();
                            $post->setCreatedBy($this->getReference("user-{$userCount}"));
                            $post->setDescription("User #{$userCount} - Board #{$boardCount} - Post #{$postCount}");
                            $post->setBoard($this->getReference("user-{$userCount}-board-{$boardCount}"));

                            $dm->persist($post);
                            $dm->flush($post);
                            $this->addReference("user-{$userCount}-board-{$boardCount}-post-{$postCount}", $post);
                        }

                    }
                }

            }
        }

        $dm->flush();

        // For each User...
        for ($userCount = 1; $userCount <= $GLOBALS['FIXTURE_USERS_TOTAL']; $userCount++) {
            if ($this->hasReference("user-{$userCount}")) {

                // ... and for each User's Board...
                for ($boardCount = 1; $boardCount <= $GLOBALS['FIXTURE_BOARDS_TOTAL']; $boardCount++) {
                    if ($this->hasReference("user-{$userCount}-board-{$boardCount}")) {

                        // ... and for each Board's Post...
                        for ($postCount = 1; $postCount <= $GLOBALS['FIXTURE_POSTS_TOTAL']; $postCount++) {
                            if ($this->hasReference("user-{$userCount}-board-{$boardCount}-post-{$postCount}")) {

                                // ... create 1 Re-Post
                                $nextUser = $userCount + 1;
                                if (!$this->hasReference("user-{$nextUser}")) {
                                    $nextUser = 1;
                                }

                                $post = new Post();
                                $post->setParent($this->getReference("user-{$userCount}-board-{$boardCount}-post-{$postCount}"));
                                $post->setCreatedBy($this->getReference("user-{$nextUser}"));
                                $post->setDescription("User #{$userCount} - Board #{$boardCount} - Post #{$postCount} - Re-Post");
                                $post->setBoard($this->getReference("user-{$nextUser}-board-{$boardCount}"));

                                $dm->persist($post);
                                $dm->flush($post);
                                $this->addReference("user-{$nextUser}-board-{$boardCount}-repost-{$postCount}", $post);
                            }
                        }

                    }
                }

            }
        }
    }
}