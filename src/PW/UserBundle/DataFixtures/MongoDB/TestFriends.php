<?php

namespace PW\UserBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\AbstractFixture,
    Doctrine\Common\DataFixtures\OrderedFixtureInterface,
    Doctrine\Common\Persistence\ObjectManager,
    Doctrine\ODM\MongoDB\DocumentManager,
    PW\BoardBundle\Document\Board,
    PW\UserBundle\Document\User,
    PW\UserBundle\Document\Follow,
    PW\PostBundle\Document\Post,
    PW\PostBundle\Document\PostComment;

/**
 * TestFriends
 */
class TestFriends extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * getOrder
     *
     * Requires categories
     *
     * @return int
     */
    public function getOrder()
    {
        return 20;
    }

    /**
     * Load some users, make fwends
     *
     * Billy follows nobody, and is followed by nobody
     * Wendy follows Joe, Jane and Britney. is followed by Joe
     * Jane follows nobody, and is followed by Wendy
     * Joe follows Wendy, is followed by Wendy
     * Britney follows nodoby, is followed by Wendy and Joe
     *
     * Therefore Wendy and Joe are the only friends
     *
     * @param ObjectManager $dm instance
     */
    public function load(ObjectManager $dm)
    {
        $defaults = array(
            'plainPassword' => 'test',
            'enabled' => true,
            'created' => new \DateTime(),
        );

        $billy = new User();
        $billy->fromArray($defaults);
        $billy->setEmail("billy@example.com");
        $billy->setName("Billy Nomates");
        $billy->setUsername('billy');
        $dm->persist($billy);
        $this->addReference("Billy", $billy);

        $wendy = new User();
        $wendy->fromArray($defaults);
        $wendy->setEmail("wendy@example.com");
        $wendy->setName("Wendy Friendly");
        $wendy->setUsername('wendy');
        $dm->persist($wendy);
        $this->addReference("Wendy", $wendy);

        $jane = new User();
        $jane->fromArray($defaults);
        $jane->setEmail("jane@example.com");
        $jane->setName("Jane Plain");
        $jane->setUsername('jane');
        $dm->persist($jane);
        $this->addReference("Jane", $jane);

        $joe = new User();
        $joe->fromArray($defaults);
        $joe->setEmail("joe@example.com");
        $joe->setName("Joe Schmo");
        $joe->setUsername("joe");
        $dm->persist($joe);
        $this->addReference("Joe", $joe);

        $britney = new User();
        $britney->fromArray($defaults);
        $britney->setEmail("britney@example.com");
        $britney->setName("Britney Popular");
        $britney->setUsername("britney");
        $dm->persist($britney);
        $this->addReference("Britney", $britney);

        $follow = new Follow();
        $follow->setFollower($wendy);
        $follow->setFollowing($joe);
        $follow->setIsFriend(true);
        $dm->persist($follow);
        $this->addReference("Wendy follows Joe", $follow);

        $follow = new Follow();
        $follow->setFollower($wendy);
        $follow->setFollowing($britney);
        $dm->persist($follow);
        $this->addReference("Wendy follows Britney", $follow);

        $follow = new Follow();
        $follow->setFollower($joe);
        $follow->setFollowing($wendy);
        $follow->setIsFriend(true);
        $dm->persist($follow);
        $this->addReference("Joe follows Wendy", $follow);

        // Data used in notification tests
        $billysBoard = new Board();
        $billysBoard->setCreatedBy($billy);
        $billysBoard->setCategory($this->getReference("Category-1"));
        $billysBoard->setIsActive(true);
        $billysBoard->setName('Billy\'s Board');
        $dm->persist($billysBoard);
        $this->addReference("Billy's board", $billysBoard);

        $wendysBoard = new Board();
        $wendysBoard->setCreatedBy($wendy);
        $wendysBoard->setCategory($this->getReference("Category-1"));
        $wendysBoard->setIsActive(true);
        $wendysBoard->setName('Wendy\'s Board');
        $dm->persist($wendysBoard);
        $this->addReference("Wendy's board", $wendysBoard);

        $follow = new Follow();
        $follow->setFollower($wendy);
        $follow->setFollowing($billysBoard);
        $dm->persist($follow);
        $this->addReference("Wendy follows Billy's board", $follow);

        $follow = new Follow();
        $follow->setFollower($wendy);
        $follow->setFollowing($jane);
        $dm->persist($follow);
        $this->addReference("Wendy follows Jane", $follow);

        $post = new Post();
        $post->setCreatedBy($billy);
        $post->setDescription('Billy\'s first post');
        $post->setBoard($billysBoard);
        $dm->persist($post);
        $this->addReference("Billy's first post", $post);

        $post = new Post();
        $post->setCreatedBy($wendy);
        $post->setDescription('Wendy\'s first post');
        $post->setBoard($wendysBoard);
        $dm->persist($post);
        $this->addReference("Wendy's first post", $post);

        $dm->flush();
        
        $repost = new Post();
        $repost->setCreatedBy($billy);
        $repost->setParent($post);
        $repost->setDescription('Billy\'s repost');
        $repost->setBoard($billysBoard);
        $dm->persist($repost);
        $this->addReference("Billy's repost", $repost);

        $dm->flush();

        $comment = new PostComment();
        $comment->setCreatedBy($wendy);
        $comment->setContent("Normal Comment");
        $comment->setPost($post);
        $dm->persist($comment);
        $this->addReference("Wendy's normal comment", $post);

        $dm->flush();

        $reply = new PostComment();
        $reply->setCreatedBy($joe);
        $reply->setContent("Reply");
        $reply->setPost($post);
        $dm->persist($reply);
        $this->addReference("Joe's reply", $post);
        $dm->persist($reply);

        $dm->flush();

        $comment->addSubactivity($reply);
        $dm->persist($reply);

        $dm->flush();

        $comment = new PostComment();
        $comment->setCreatedBy($billy);
        $comment->setContent("Check this out @joe");
        $comment->setPost($post);
        $dm->persist($comment);
        $this->addReference("Billy's tagged comment", $post);

        $dm->flush();
    }
}
