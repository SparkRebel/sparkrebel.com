<?php

namespace PW\UserBundle\Command;

use PW\UserBundle\Document\Follow,
    Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;

/**
 * When a new user follows another user - Create some backdated feed data for them
 */
class FollowBoardCommand extends ContainerAwareCommand
{

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
     * document manager placeholder instance
     */
    protected $dm;

    /**
     * configure
     */
    protected function configure()
    {
        $this
            ->setName('follow:board')
            ->setDescription('Create stream entries for the boards latest activity')
            ->setDefinition(array(
                new InputArgument(
                    'follower',
                    InputArgument::REQUIRED,
                    'The follower $id'
                ),
                new InputArgument(
                    'board',
                    InputArgument::REQUIRED,
                    'The board $id'
                )
            ));
    }

    /**
     * execute
     *
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->maxQueueSize = $this->getContainer()->getParameter('redis.max_queue_size', 100);

        $followerId = $input->getArgument('follower');
        $followsId = $input->getArgument('board');

        $this->dm = $this->getContainer()
            ->get('doctrine_mongodb.odm.document_manager');
        $this->postRepo = $this->dm->getRepository('PWPostBundle:Post');
        $userRepo = $this->dm->getRepository('PWUserBundle:User');
        $boardRepo = $this->dm->getRepository('PWBoardBundle:Board');

        $follows = $boardRepo->find($followsId);
        if (!$follows) {
            throw new \Exception("Board $followsId (the target) doesn't exist");
        }


        if ($follows->getCreatedBy()->isCeleb()) {
            $output->writeln("<error>Skipping board $followsId. Celeb board</error>");
            return false;
        }

        $postType = $follows->getCreatedBy()->getUserType();
        if ($postType === 'merchant') {
            $postType = 'brand';
        }



        $conditions = array(
            'board.$id' => new \MongoId($followsId),
        );
        $latest = $this->postRepo
            ->findBy($conditions)
            ->sort(array('created' => 'desc'))
            ->limit(20);

        $count = count($latest);
        if (!$count) {
            $output->writeln("<comment>No posts found to process</comment>");
            return;
        }

        foreach ($latest as $post) {
            $weight = 1;
            $postId = $post->getId();
            $created = $post->getCreated();
            if ($created) {
                $created = $created->getTimestamp();
            }
            $score = str_pad($this->maxScore - ($created  * $weight), strlen($this->maxScore), '0', STR_PAD_LEFT);
            $this->processFollow($followerId, $postId, $postType, $score, $output);
        }

        $this->dm->flush(null, array('safe' => false, 'fsync' => false));
        $output->writeln("<info>Added {$count} post(s) to user {$followerId} {$postType} stream</info>");
    }

    /**
     * Add a record for one user and one post
     *
     * @param string $userId
     * @param string $postId
     * @param string $type user|brand
     * @param int $score
     * @param OutputInterface $output
     */
    protected function processFollow($userId, $postId, $type = 'user', $score = 0, OutputInterface $output = null)
    {
        if ($output) {
            $output->writeln("<info>Adding post {$postId} to user {$userId} {$type} stream</info>");
        }

        $redis = $this->getContainer()->get('snc_redis.default');
        $key = 'stream:{' . $userId . '}:' . $type;
        $redis->zadd($key, $score, $postId);
        $count = $redis->zcard($key);
        if ($count > $this->maxQueueSize) {
            $redis->zremrangebyrank($key, $this->maxQueueSize, -1);
        }
    }
}
