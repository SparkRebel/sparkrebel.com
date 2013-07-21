<?php

namespace PW\UserBundle\Command;

use PW\UserBundle\Document\Follow;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * When a new user follows another user - Create some backdated feed data for them
 */
class StreamRefreshCommand extends ContainerAwareCommand
{
    /**
     * The max number of posts any user's stream will hold
     */
    protected $maxQueueSize = 500;

    /**
     * Items need to be stored with decreasing scores. This is the the timestamp for 2038
     * We'll subtract the current timestamp from this to get the default score
     */
    protected $maxScore = 2147483647;

    protected $dm;
    protected $type;
    protected $redis;
    protected $followRepo;
    protected $userManager;
    protected $eventManager;
    protected $outpout;

    protected function configure()
    {
        $this
            ->setName('stream:refresh')
            ->setDescription('Wipe a users stream, and rebuild it')
            ->setDefinition(array(
                new InputArgument('userId', InputArgument::REQUIRED, 'The user $id ("*" for all, "brands" for brands)'),
                new InputArgument('type', InputArgument::REQUIRED, 'Which stream(s) to rebuild: user or brand')
            ));
    }

    /**
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->maxQueueSize = $this->getContainer()->getParameter('redis.max_queue_size', 100);
        $this->type = $input->getArgument('type');
        
        if ($this->type == 'brand') {
            // we dont need to refresh brand stream -> its fetched from db in StreamController
            return true;
        }

        $this->dm = $this->getContainer()->get('doctrine_mongodb.odm.document_manager');
        $this->redis = $this->getContainer()->get('snc_redis.default');

        $this->postRepo = $this->dm->getRepository('PWPostBundle:Post');
        $this->itemRepo = $this->dm->getRepository('PWItemBundle:Item');
        $this->followRepo = $this->dm->getRepository('PWUserBundle:Follow');

        /* @var $userManager \PW\UserBundle\Model\UserManager */
        $this->userManager  = $this->getContainer()->get('pw_user.user_manager');
        $this->eventManager = $this->getContainer()->get('pw.event');

        $users  = false;
        $userId = $input->getArgument('userId');
        if ($userId == '*') {
            $users = $this->userManager->getRepository()
                ->findByType('user', null, false)
                ->field('isActive')->equals(true)
                ->getQuery()->execute();
        } else if ($userId === 'brands') {
            $users = $this->userManager->getRepository()
                ->findByType('brand')
                ->field('isActive')->equals(true)
                ->getQuery()->execute();
        }

        if ($users !== false) {
            foreach ($users as $user) {
                $this->eventManager->requestJob("stream:refresh {$user->getId()} {$this->type}", 'normal', '', '', 'feeds');
                if ($this->type == 'user') {
                    // turn off for a while - too much load
                    //$this->eventManager->requestJob("stream:refresh {$user->getId()} onsale", 'low', '', '', 'feeds');
                } else if ($this->type == 'brand') {
                    $this->eventManager->requestJob("stream:refresh {$user->getId()} brandOnsale", 'low', '', '', 'feeds');
                }
            }
        } else {
            $this->processFollower($userId);
            if ($this->type == 'user') {
                //$this->eventManager->requestJob("stream:refresh {$userId} onsale", 'low', '', '', 'feeds');
            } else if ($this->type == 'brand') {
                $this->eventManager->requestJob("stream:refresh {$userId} brandOnsale", 'low', '', '', 'feeds');
            }
        }
    }

    /**
     * refreshes stream of one user
     *
     * @param string $followerId
     */
    protected function processFollower($followerId) {

        if ($follower = $this->userManager->find($followerId)) {
            $this->output->writeln("<info>Processing User {$follower->getName()}</info>");
        } else {
            $this->output->writeln("<error>Couldn't find User with ID: {$followerId}</error>");
            return;
        }

        $conditions = array();
        $needsClear = false;

        if ($this->type == 'brandOnsale') {
            if ($follower->getType() != 'brand') {
                $this->output->writeln("<comment>User {$followerId} isn't a brand</comment>");
                return;
            }

            $conditions['brandUser.$id'] = new \MongoId($followerId);

            //Clear the stream on regen, since items could be removed from the stream
            $needsClear = true;
        } else {
            if ($this->type === 'onsale') {
                $follows = $this->followRepo->findFollowingByUser($follower, 'users')->limit(100);
            } else {
                $follows = $this->followRepo->findFollowingByUser($follower, 'boards')->limit(120);
            }
            $follows->field('isActive')->equals(true);;
            $follows->field('deletedBy')->equals(null); // exclude "deleted" follows
            $follows->field('isCeleb')->in(array(null, false));
            if ($this->type === 'user') {
                $follows->field('user.type')->equals('user');
            } elseif ($this->type === 'onsale') {
                $follows->field('target.type')->in(array('brand','merchant'));
            } else { //type == board etc
                $follows->field('user.type')->notEqual('user');
            }

            $follows = $follows->getQuery()->execute();

            $this->redis->zremrangebyrank('stream:{' . $followerId . '}:' . $this->type, 0, $this->maxQueueSize);
            if (!count($follows)) {
                $this->output->writeln("<comment>User {$followerId} isn't following anything</comment>");
                return;
            }
            
            //$onsalePostsConditions = array('target.$ref'=>'items');
            $cache_merchantUser_ids = array();
            
            foreach ($follows as $follow) {
                $target = $follow->getTarget();
                $class = get_class($target); //TODO bad habits..
                
                if ($this->type == 'onsale') {
                    //$user = $follow->getUser();
                    $this->output->writeln("<info>onsale</info>");
                    if (!isset($cache_merchantUser_ids[$target->getId()]))
                    {
                        $conditions['merchantUser.$id']['$in'][] = new \MongoId($target->getId());
                        //$conditions['brandUser.$id']['$in'][] = new \MongoId($target->getId());
                        $cache_merchantUser_ids[$target->getId()] = true;
                    }
                    //$onsalePostsConditions['board.$id']['$in'][] = new \MongoId($target->getId());
                    $this->output->writeln('<info>Adding items from brand ' . $target->getId()
                        //. ' ('.$target->getName().') '
                        . '</info>');
                } elseif (strpos($class, 'User')) {
                    $conditions['createdBy.$id']['$in'][] = new \MongoId($target->getId());
                    $this->output->writeln('<info>Adding items from user ' . $target->getId() . '</info>');
                } else {
                    $conditions['board.$id']['$in'][] = new \MongoId($target->getId());
                    $this->output->writeln('<info>Adding posts from board ' . $target->getId() 
                        //. ' ('.$target->getName().')'
                        . '</info>');          
                }
                //$this->output->writeln(sprintf("<info>Found follow (%s) for target '%s'</info>", $follow->getId(), $target->getId()));
            }
        }

        if (!$conditions) {
            $this->output->writeln("<comment>User {$followerId} isn't following anything for {$this->type} stream</comment>");
            return;
        }

        $onsaleStream = ($this->type == 'onsale' || $this->type == 'brandOnsale');
        
        if ($onsaleStream) {
            $conditions['isOnSale'] = true;
            $latest = $this->itemRepo
                ->findBy($conditions)
                ->sort(array('created' => 'desc'))
                ->limit(intval($this->maxQueueSize));
                
            /*if ($this->type == 'onsale') {
                foreach ($latest as $item) {
                    $onsalePostsConditions['target.$id']['$in'][] = $item->getId(); 
                }
                $latest = $this->postRepo
                    ->findBy($onsalePostsConditions)
                    ->sort(array('created' => 'desc'))
                    ->limit($this->maxQueueSize);
            }*/
        } else {
            $latest = $this->postRepo
                ->findBy($conditions)
                ->sort(array('created' => 'desc'))
                ->limit($this->maxQueueSize);
        }

        $count = count($latest);
        if (!$count) {
            $this->output->writeln("<comment>No posts found to process</comment>");
            return;
        }

        if ($needsClear) {
            $this->redis->del('stream:{' . $followerId . '}:' . $this->type);
        }

        $weight = 1;
        $countAdded = 0;
        foreach ($latest as $post) {
            $postId = $post->getId();
            if ($onsaleStream && $rootPost = $post->getRootPost()) {
                $postId = $rootPost->getId();
            }

            $created = $post->getCreated();
            if ($created) {
                $created = $created->getTimestamp();
            }
            $score = str_pad($this->maxScore - ($created  * $weight), strlen($this->maxScore), '0', STR_PAD_LEFT);
            $this->processFollow($followerId, $postId, $score);
            $countAdded++;
        }

        $this->output->writeln("<info>Added {$countAdded} post(s) to user {$followerId} {$this->type} stream</info>");
    }


    /**
     * Add a record for one user and one post
     *
     * @param string $userId id
     * @param string $postId id
     * @param int    $score  defaults to 0
     */
    protected function processFollow($userId, $postId, $score = 0)
    {
        $this->output->writeln("<info>Adding post {$postId} to user {$userId} {$this->type} stream</info>");

        $key = 'stream:{' . $userId . '}:' . $this->type;
        $this->redis->zadd($key, $score, $postId);
        $count = $this->redis->zcard($key);
        if ($count > $this->maxQueueSize) {
            $this->redis->zremrangebyrank($key, $this->maxQueueSize, -1);
        }
    }
}
