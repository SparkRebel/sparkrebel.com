<?php

namespace PW\PostBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\AbstractFixture,
    Doctrine\Common\DataFixtures\OrderedFixtureInterface,
    Doctrine\Common\Persistence\ObjectManager,
    PW\PostBundle\Document\Post,
    PW\PostBundle\Document\PostComment;

class LoadExamplePosts extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Almost everything must exist before posts
     *
     * @return int
     */
    public function getOrder()
    {
        return 70;
    }

    /**
     * @param mixed $manager instance
     */
    public function load(ObjectManager $manager)
    {
        for ($j = 1; $j <= 30; $j++) {
            $i = ceil($j / 3);
            $post = new Post();

            $comment1 = new PostComment();
            $comment1->setPost($post);
            $comment1->setContent('This is the first comment.');
            $comment1->setCreatedBy($this->getReference("User-{$i}"));
            $manager->persist($comment1);
            $post->addActivity($comment1);

            $comment1 = new PostComment();
            $comment1->setPost($post);
            $comment1->setContent('This is the second comment.');
            $comment1->setCreatedBy($this->getReference("User-{$i}"));
            $manager->persist($comment1);
            $post->addActivity($comment1);

            $comment2 = new PostComment();
            $comment2->setPost($post);
            $comment2->setContent('This is the first reply to the second comment.');
            $comment2->setCreatedBy($this->getReference("User-{$i}"));
            $manager->persist($comment2);
            $comment1->addSubactivity($comment2);

            $post->setCreated(time() - $j * 100);
            $post->setCreatedBy($this->getReference("User-{$i}"));
            $post->setBoard($this->getReference("Board-{$i}"));
            $this->getReference("Board-{$i}")->incPostCount();
            $post->setTarget($this->getReference("Item-{$i}"));
            $post->setDescription('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas pulvinar eleifend nulla, id varius est venenatis sed. Vestibulum velit est, tempus at tincidunt eu, volutpat at nunc. Aliquam erat volutpat.');
            $post->setImage($this->getReference('Asset-Blank'));
            $post->setLink("/relative/link/$i");
            if ($j % 2 == 0) {
                $post->setParent($this->getReference("Post-" . ($j - 1)));
            } else {
                $post->setRepostCount(1);
            }
            $this->addReference("Post-$j", $post);
            $manager->persist($post);
        }

        $manager->flush();
    }
}
