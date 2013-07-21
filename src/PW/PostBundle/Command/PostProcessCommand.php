<?php

namespace PW\PostBundle\Command;

use PW\ApplicationBundle\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use PW\PostBundle\Document\StreamItem;

/**
 * When a new post is created, we need to create references in the follow_posts collection for all
 * followers.
 */
class PostProcessCommand extends AbstractCommand
{
    /**
     * post repo placeholder instance
     */
    protected $postRepo;

    /**
     * maxQueueSize
     *
     * The max number of posts any user's stream will hold
     */
    protected $maxQueueSize = 100;

    /**
     * maxScore
     *
     * Items need to be stored with decreasing scores. This is the the timestamp for 2038
     * We'll subtract the current timestamp from this to get the default score
     */
    protected $maxScore = 2147483647;

    /**
     * configure
     */
    protected function configure()
    {
        $this
            ->setName('follow:post')
            ->setDescription('Create stream entries for each user following the board or author, and add to the category stream')
            ->setDefinition(array(
                new InputArgument(
                    'id',
                    InputArgument::REQUIRED,
                    'The post $id'
                )
            ));
    }

    /**
     * execute
     *
     * Find the followers for boards and add a stream entry for the newly created
     * Find the board's category and add a stream entry to it as well
     * post for each follower
     *
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->maxQueueSize = $this->getContainer()->getParameter('redis.max_queue_size', 100);

        $limit = 10;

        $id = $input->getArgument('id');

        $this->dm = $this->getContainer()->get('doctrine_mongodb.odm.document_manager');
        $this->postRepo = $this->dm->getRepository('PWPostBundle:Post');
        $followRepo = $this->dm->getRepository('PWUserBundle:Follow');

        $post = $this->postRepo->find($id);
        if (!$post) {
            $output->writeln("<error>Post {$id} doesn't exist</error>");
            return;
        }

        if (!$post->getBoard()) {
            $output->writeln("<error>Post has no Board</error>");
            return;
        }

        if ($post->getCreatedBy()->isCeleb()) {
            $output->writeln("<error>Skipping celeb post</error>");
            return;
        }

        $conditions = array(
            'target.$ref' => 'boards',
            'target.$id' => new \MongoId($post->getBoard()->getId())
        );

        $weight = 1;
        $postId = $post->getId();
        $postType = $post->getUserType();
        $created = $post->getCreated()->getTimestamp();
        $score = str_pad($this->maxScore - ($created  * $weight), strlen($this->maxScore), '0', STR_PAD_LEFT);

        // Add the post to the user's own stream
        if ($postType === 'user') {
            $this->processFollow($post->getCreatedBy(), $post, $postType, $score, $output);
        } else {
            $postType = 'brand';
        }

        while (true) {
            $follows = $followRepo
                ->findBy($conditions)
                ->sort(array('id' => true))
                ->limit($limit);

            $follow = null;
            foreach ($follows as $follow) {
                if($follow->getDeleted()) {
                    continue;
                }


                $this->processFollow($follow->getFollower(), $post, $postType, $score, $output);
            }

            if (!$follow) {
                break;
            }

            $lastId = $follow->getId();
            $conditions['id']['$gt'] = new \MongoId($lastId);

            $this->dm->flush(null, array('safe' => false, 'fsync' => false));
        }

        //add to category
        $category = $post->getCategory();
        if (!empty($category)) {
            $this->processCategory($postId, $category->getId(), $score, $output);
        }
    }

    /**
     * add a record for one user and one post.
     *
     * After adding the latest entry, if that pushes up to the limit - gc the dead entries
     *
     * @param mixed  $user   instance
     * @param Post $post   instance
     * @param string $type   the post type, user or brand
     * @param int    $score  the score to assign to this record
     */
    protected function processFollow($user, $post, $type = 'user', $score = 0, OutputInterface $output = null)
    {
        if ($type == 'brand') {
            return; // brand streams in redis no longer needed (in StreamController we do db query to get brand posts)
        }
        
        $userId = $user->getId();
        $postId = $post->getId();
        if ($output) {
            $output->writeln("<info>Adding post {$postId} to user {$userId} {$type} stream</info>");
        }

        /*
         * We save StreamItem to recreate redis stream easly next time   
         */
        $si = new StreamItem;
        $si
            ->setUser($user)
            ->setPost($post)
            ->setType($type)
            ->setScore($score)
        ;    
        $this->dm->persist($si);
        $this->dm->flush(null, array('safe' => false, 'fsync' => false));

        $redis = $this->getContainer()->get('snc_redis.default');
        $key = 'stream:{' . $userId . '}:' . $type;
        $redis->zadd($key, $score, $postId);
        $count = $redis->zcard($key);
        if ($count > $this->maxQueueSize) {
            $redis->zremrangebyrank($key, $this->maxQueueSize, -1);
        }
    }

    /**
     * add a record for one category and one post.
     *
     * After adding the latest entry, if that pushes up to the limit - gc the dead entries
     *
     * @param string $postId     id
     * @param string $categoryId the post category
     * @param int    $score      the score to assign to this record
     */
    protected function processCategory($postId, $categoryId, $score = 0, OutputInterface $output = null)
    {
        if ($output) {
            $output->writeln("<info>Adding post {$postId} to category {$categoryId} stream</info>");
        }

        $redis = $this->getContainer()->get('snc_redis.default');
        $key = 'stream:category:{' . $categoryId . '}';
        $redis->zadd($key, $score, $postId);
        $count = $redis->zcard($key);
        if ($count > $this->maxQueueSize) {
            $redis->zremrangebyrank($key, $this->maxQueueSize, -1);
        }
    }


}
