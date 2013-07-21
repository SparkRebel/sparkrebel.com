<?php

namespace PW\ActivityBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\AbstractFixture,
    Doctrine\Common\DataFixtures\OrderedFixtureInterface,
    Doctrine\Common\Persistence\ObjectManager,
    Doctrine\ODM\MongoDB\DocumentManager,
    PW\ActivityBundle\Document\Activity;

/**
 * TestFriendsActivity
 */
class TestFriendsActivity extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * getOrder
     *
     * Requires test friends
     *
     * @return int
     */
    public function getOrder()
    {
        return 30;
    }

    /**
     * Load user activity for TestFriends fixture
     *
     * @param ObjectManager $dm instance
     */
    public function load(ObjectManager $dm)
    {
        $board = $this->getReference("Billy's board");
        $activity = new Activity();
        $activity->setType('board.create');
        $activity->setTarget($board);
        $activity->setUser($board->getCreatedBy());
        $activity->setCreated($board->getCreated());
        $dm->persist($activity);

        $board = $this->getReference("Wendy's board");
        $activity = new Activity();
        $activity->setType('board.create');
        $activity->setTarget($board);
        $activity->setUser($board->getCreatedBy());
        $activity->setCreated($board->getCreated());
        $dm->persist($activity);

        $follow = $this->getReference("Wendy follows Billy's board");
        $activity = new Activity();
        $activity->setType('board.follow');
        $activity->setTarget($follow);
        $activity->setUser($follow->getFollower());
        $activity->setCreated($follow->getCreated());
        $dm->persist($activity);

        $follow = $this->getReference("Wendy follows Jane");
        $activity = new Activity();
        $activity->setType('user.follow');
        $activity->setTarget($follow);
        $activity->setUser($follow->getFollower());
        $activity->setCreated($follow->getCreated());
        $dm->persist($activity);

        $follow = $this->getReference("Wendy follows Joe");
        $activity = new Activity();
        $activity->setType('user.follow');
        $activity->setTarget($follow);
        $activity->setUser($follow->getFollower());
        $activity->setCreated($follow->getCreated());
        $dm->persist($activity);

        $follow = $this->getReference("Joe follows Wendy");
        $activity = new Activity();
        $activity->setType('user.follow');
        $activity->setTarget($follow);
        $activity->setUser($follow->getFollower());
        $activity->setCreated($follow->getCreated());
        $dm->persist($activity);

        $post = $this->getReference("Billy's first post");
        $activity = new Activity();
        $activity->setType('post.create');
        $activity->setTarget($post);
        $activity->setUser($post->getCreatedBy());
        $activity->setCreated($post->getCreated());
        $dm->persist($activity);

        $post = $this->getReference("Wendy's first post");
        $activity = new Activity();
        $activity->setType('post.create');
        $activity->setTarget($post);
        $activity->setUser($post->getCreatedBy());
        $activity->setCreated($post->getCreated());
        $dm->persist($activity);

        $comment = $this->getReference("Wendy's normal comment");
        $activity = new Activity();
        $activity->setType('comment.create');
        $activity->setTarget($comment);
        $activity->setUser($comment->getCreatedBy());
        $activity->setCreated($comment->getCreated());
        $dm->persist($activity);

        $comment = $this->getReference("Billy's tagged comment");
        $activity = new Activity();
        $activity->setType('comment.create');
        $activity->setTarget($comment);
        $activity->setUser($comment->getCreatedBy());
        $activity->setCreated($comment->getCreated());
        $dm->persist($activity);

        $dm->flush();
    }
}
