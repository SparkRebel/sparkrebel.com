<?php

namespace PW\UserBundle\Command;

use PW\UserBundle\Document\Follow;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PW\PostBundle\Document\StreamItem;

/**
 * When a new user follows another user - Create some backdated feed data for them
 */
class BuildStreamCommand extends ContainerAwareCommand
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
    protected $type = 'user';
    protected $redis;
    protected $followRepo;
    protected $userManager;
    protected $eventManager;
    protected $outpout;
    protected $follower;
    protected function configure()
    {
        $this
            ->setName('stream:build')
            ->setDescription('Builds new users stream')
            ->setDefinition(array(
                new InputArgument('userId', InputArgument::REQUIRED, 'The user $id'),
            ));
    }

    /**
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
         
        $this->dm = $this->getContainer()->get('doctrine_mongodb.odm.document_manager');
        $this->redis = $this->getContainer()->get('snc_redis.default');

        $this->postRepo = $this->dm->getRepository('PWPostBundle:Post');
        $this->itemRepo = $this->dm->getRepository('PWItemBundle:Item');
        $this->followRepo = $this->dm->getRepository('PWUserBundle:Follow');

        /* @var $userManager \PW\UserBundle\Model\UserManager */
        $this->userManager  = $this->getContainer()->get('pw_user.user_manager');
        $this->eventManager = $this->getContainer()->get('pw.event');
        
        $userId = $input->getArgument('userId');
        
        if ($follower = $this->userManager->find($userId)) {
            $this->output->writeln("<info>Processing User {$follower->getName()}</info>");
        } else {
            $this->output->writeln("<error>Couldn't find User with ID: {$userId}</error>");
            return;
        }
        $this->follower = $follower;
        $needsClear = true;
        if ($needsClear) {
            $this->output->writeln("<info>Wiping stream for User {$follower->getName()}</info>");
            $this->redis->del('stream:{' . $userId . '}:' . $this->type);
        }

        $this->processFollower($userId);
        $this->processCelebs($userId);
        $this->processBrands($userId);
        
    }

    /**
     * builds stream from user follows
     *
     * @param string $followerId
     */
    protected function processFollower($followerId) {

        $follower = $this->follower;
              
        $follows = $this->followRepo->findFollowingByUser($follower, 'boards')->limit(120);
        $follows->field('isActive')->equals(true);;
        $follows->field('isCeleb')->in(array(null, false));   
        $follows->field('user.type')->equals('user');     
        $follows = $follows->getQuery()->execute();

        if (!count($follows)) {
            $this->output->writeln("<comment>User {$followerId} isn't following anything</comment>");
            return;
        }
      
        $conditions = array();
        foreach ($follows as $follow) {
            $target = $follow->getTarget();
            $class = get_class($target); //TODO bad habits..
                        
            if (strpos($class, 'User')) {
                $conditions['createdBy.$id']['$in'][] = new \MongoId($target->getId());
                $this->output->writeln('<info>Adding items from user ' . $target->getId() . '</info>');
            } else {
                $conditions['board.$id']['$in'][] = new \MongoId($target->getId());
                $this->output->writeln('<info>Adding posts from board ' . $target->getId() . '</info>');          
            }                
        }
        
        $latest = $this->postRepo
            ->findBy($conditions)
            ->sort(array('created' => 'desc'))
            ->limit($this->maxQueueSize);
        

        $count = count($latest);
        if (!$count) {
            $this->output->writeln("<comment>No posts found to process</comment>");
            return;
        }
       
        $countAdded = 0;
        foreach ($latest as $post) {
            $this->processPost($post, $followerId);
            $countAdded++;
        }

        $this->output->writeln("<info>Added {$countAdded} post(s) to user {$followerId} {$this->type} stream</info>");
    }

    protected function processCelebs($followerId)
    {
        $follower = $this->follower;
              
        $follows = $this->followRepo->findFollowingByUser($follower, 'boards')->limit(100);
        $follows->field('isActive')->equals(true);        
        $follows->field('isCeleb')->equals(true);     
        $follows = $follows->getQuery()->execute();
        
        if (!count($follows)) {
            $this->output->writeln("<comment>User {$followerId} isn't following any celebs</comment>");
            return;
        }

        $conditions = array();
        foreach ($follows as $follow) {
            $target = $follow->getTarget();
            
            $conditions['board.$id']['$in'][] = new \MongoId($target->getId());
            $this->output->writeln('<info>Adding celeb posts from board ' . $target->getName() . '</info>');                      
        }
        $latest = $this->postRepo
            ->findBy($conditions)
            ->sort(array('created' => 'desc'))
            ->limit(1000);            
        $countAdded = 0;
        foreach ($latest as $post) {
            //2% chance
            if(rand(1, 50) !== 1) {
                continue;
            }
            $this->processPost($post, $followerId);
            $countAdded++;
        }

        $this->output->writeln("<info>Added {$countAdded} celeb post(s) to user {$followerId} {$this->type} stream</info>");
    }


    protected function processBrands($followerId)
    {
        $follower = $this->follower;

        $follows = $this->followRepo->findFollowingByUser($follower, 'users')->limit(100);
        $follows->field('isActive')->equals(true);        
        $follows->field('target.type')->in(array('brand','merchant'));

        $follows = $follows->getQuery()->execute();

        if (!count($follows)) {
            $this->output->writeln("<comment>User {$followerId} isn't following any celebs</comment>");
            return;
        }

        $conditions = array();

        foreach ($follows as $follow) {
            $target = $follow->getTarget();
            $conditions['createdBy.$id']['$in'][] = new \MongoId($target->getId());
            $this->output->writeln('<info>Adding items from brand ' . $target->getName() . '</info>');
            
        }    

        $latest = $this->postRepo
            ->findBy($conditions)
            ->sort(array('created' => 'desc'))
            ->limit(1000);            

        
        $countAdded = 0;
        foreach ($latest as $post) {
            //2% chance
            if(rand(1, 50) !== 1) {
                continue;
            }
            $this->processPost($post, $followerId);
            $countAdded++;
        }

        $this->output->writeln("<info>Added {$countAdded} brand post(s) to user {$followerId} {$this->type} stream</info>");

       

    }

    protected function processPost($post, $followerId)
    {
        $this->output->writeln("<info>Adding post {$post->getId()} to user {$followerId} {$this->type} stream</info>");

        $weight = 1;
        $postId = $post->getId();            
        $created = $post->getCreated();
        if ($created) {
            $created = $created->getTimestamp();
        }
        $score = str_pad($this->maxScore - ($created  * $weight), strlen($this->maxScore), '0', STR_PAD_LEFT);


        $key = 'stream:{' . $followerId . '}:' . $this->type;
        $this->redis->zadd($key, $score, $postId);
        $count = $this->redis->zcard($key);

        $si = new StreamItem;
        $si
          ->setUser($this->follower)
          ->setPost($post)
          ->setType($this->type)
          ->setScore($score)
        ;
        $this->dm->persist($si);
        $this->dm->flush(null, array('safe' => false, 'fsync' => false));
        
    }
    
}