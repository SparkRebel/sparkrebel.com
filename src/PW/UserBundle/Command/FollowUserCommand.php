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
class FollowUserCommand extends ContainerAwareCommand
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
            ->setName('follow:user')
            ->setDescription('Create stream entries for the follows user\'s latest activity')
            ->setDefinition(array(
                new InputArgument(
                    'follower',
                    InputArgument::REQUIRED,
                    'The follower $id'
                ),
                new InputArgument(
                    'follows',
                    InputArgument::REQUIRED,
                    'The follows $id'
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

        /* @var $userManager \PW\UserBundle\Model\UserManager */
        $userManager = $this->getContainer()->get('pw_user.user_manager');

        $followerId = $input->getArgument('follower');
        $followsId  = $input->getArgument('follows');

        /* @var $follower \PW\UserBundle\Document\User */
        $follower = $userManager->find($followerId);
        if (!$follower) {
            $output->writeln("<error>Follower {$followerId} could not be found</error>");
            return;
        }

        /* @var $follows \PW\UserBundle\Document\User */
        $follows = $userManager->find($followsId);
        if (!$follows) {
            $output->writeln("<error>Follows {$followsId} could not be found</error>");
            return;
        } else {
            $postType = $follows->getUserType();
            if ($postType === 'merchant') {
                $postType = 'brand';
            }
        }

        /* @var $boardManager \PW\BoardBundle\Model\BoardManager */
        $boardManager = $this->getContainer()->get('pw_board.board_manager');

        // Follow this User's Boards
        $targetBoards = $boardManager->getRepository()
            ->findByUser($follows)
            ->getQuery()->execute();

        /* @var $followManager \PW\UserBundle\Model\FollowManager */
        $followManager = $this->getContainer()->get('pw_user.follow_manager');

        foreach ($targetBoards as $board /* @var $board \PW\BoardBundle\Document\Board */) {
            $boardFollow = $followManager->addFollower($follower, $board);
            $boardFollow->setNoEmit(true);
            $followManager->update($boardFollow, false);
        }
        $followManager->flush();

        /* @var $postManager \PW\PostBundle\Model\PostManager */
        $postManager = $this->getContainer()->get('pw_post.post_manager');

        $userPosts = $postManager->getRepository()
            ->findByUser($follows)->limit(20)
            ->getQuery()->execute();

        $count = count($userPosts);
        if (!$count) {
            $output->writeln(sprintf("<comment>No posts found for user '%s'</comment>", $follows->getName()));
            return;
        }

        foreach ($userPosts as $post /* @var $post \PW\PostBundle\Document\Post */) {
            $weight  = 1;
            $postId  = $post->getId();
            $created = $post->getCreated();
            if ($created) {
                $created = $created->getTimestamp();
            }
            $score = str_pad($this->maxScore - ($created * $weight), strlen($this->maxScore), '0', STR_PAD_LEFT);
            $this->processFollow($followerId, $postId, $postType, $score, $output);
        }

        $postManager->flush();
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
        if ($type == 'brand') {
            return; // brand streams in redis no longer needed (in StreamController we do db query to get brand posts)
        }
        
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
