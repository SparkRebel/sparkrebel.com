<?php

namespace PW\ApplicationBundle\Model;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Predis\Client;
use PW\UserBundle\Document\User;
use PW\BoardBundle\Document\Board;
use Symfony\Component\DependencyInjection\ContainerAware;
/**
 * EventManager
 *
 * Provides an interface to our own delayed job/pub-sub system, not related to db events
 */
class EventManager extends ContainerAware
{

    const HIGH_PRIORITY = 0;

    const MEDIUM_PRIORITY = 1024;

    const LOW_PRIORITY = 1048576;

    
    /**
     * @var string
     */
    protected $environment;

    /**
     * @var \Predis\Client
     */
    protected $redis;

    /**
     * @var \GearmanClient
     */
    protected $jobClient;

    /**
     * @var Doctrine\ODM\MongoDB\DocumentManager
     */
    protected $dm;

    protected $pheanstalk;

    /**
     * @var array
     */
    protected $config = array(
        'env'   => null,
        'mode'  => 'background',
        'jobs'  => null,
    );

    /**
     * @var array
     */
    protected $requests = array();

    /**
     * __construct
     *
     * @param string $environment dev, test, prod
     * @param array  $config      array of config settings
     */
    public function __construct($environment, $config, DocumentManager $dm = null)
    {
        $this->config['env'] = $environment;

        if (empty($this->config['jobs'])) {
            $this->config = array_merge($this->config, $config);
        }

        $this->dm = $dm;
    }

    /**
     * Currently only sends the message to the pub sub queue. We may want some events to be
     * only/also published as job requests. We don't want to use pub sub for delayed execution
     * as all workers connected will process all jobs - i.e. n workers = the same job done n times
     *
     * @param string $event    event name
     * @param mixed  $message  array of args
     * @param string $priority priority to handle event ('high', 'normal', 'low')
     * @param string $queue named queue in which job shall be executed
     * @return mixed null atm
     */
    public function publish($event, $message, $priority = 'normal', $queue = null, $server = null)
    {        
        $this->_log('publish', compact('event', 'message', 'priority'));
        if (!empty($this->config['jobs'][$event])) {
            $this->publishJob($event, $message, $priority, $queue, $server);
        }

        return $this->publishPubSub($event, $message);
    }

    /**
     * publish a job to the job queue
     *
     * @param string $event    the name of the job to request
     * @param mixed  $message  data to publish with the job, will be json encoded
     * @param string $priority priority to handle event ('high', 'medium', 'low')
     * @param string $queue named queue in which job shall be executed
     * @return job handles for registered jobs
     */
    public function publishJob($event, $message, $priority = 'normal', $queue = null, $server = null)
    {
        $this->_log('publishJob', compact('event', 'message', 'priority'));
        if (empty($this->config['jobs'][$event])) {
            throw \Exception("{$event} job is not configured");
        }

        if (is_array($message)) {
            $keys = array_keys($message);
            foreach ($keys as &$key) {
                $key = "$$key$";
            }
        }

        $return = array();
        foreach ($this->config['jobs'][$event] as $command) {
            if (!empty($keys)) {
                $command = str_replace($keys, $message, $command);
            }
            $return[] = $this->requestJob($command, $priority, $queue, '', $server);
        }

        if (count($return) === 1) {
            return $return[0];
        }
        return $return;
    }

    /**
     * Directly request a job to be stuck in the gearman queue
     *
     * Differs from publishJob in that no manipulation of the $command arg is performed
     *
     * @param string $command  app/console cli arguments
     * @param string $priority priority to handle event ('high', 'medium', 'low')
     * @param string $queue    the job queue to use
     * @param string $uniqueId unique string used to identify the job
     * @param string $server web/feeds
     * @return command return code or gearman return code
     */
    public function requestJob($command, $priority = 'normal', $queue = '', $uniqueId = '', $server = null)
    {                
        $this->_log('requestJob', compact('command', 'priority', 'queue', 'uniqueId'));
        if (!$this->config['mode']) {
            return false;
        }

        if (!$this->jobClient && $this->config['mode'] === 'background') {
            if ($server === null) {
                $this->jobClient = $this->container->get('leezy.pheanstalk.primary');    
            } else {
                $this->jobClient = $this->container->get('leezy.pheanstalk.'.$server);    
            }
            
            //$this->jobClient = new \GearmanClient();
            //$this->jobClient->addServer();
        }

        if (!$uniqueId) {
            $uniqueId = md5(serialize($command));
        }

        if ($this->config['mode'] === 'foreground') {
            $host = $this->container->getParameter('host');
            if ($host == 'sparkrebel.com') {
                $dir = '/var/www/sparkrebel.com/current';
            } else if ($host == 'staging.sparkrebel.com') {
                $dir = '/var/www/staging.sparkrebel.com/current';
            }  else {
                $dir = $this->container->get('kernel')->getRootdir().'/../';
            }
            $command = "cd $dir && php app/console --verbose --no-debug --env={$this->config['env']} $command";
            $output = array();
            $returnVar = 1;
            if (PHP_SAPI === 'cli') {
                echo "\nrequestJob:".$command." (mode:".$this->config['mode'].")\n";
                system($command);
            } else {
                exec($command, $output, $returnVar);
            }
            
            return 1; //return $returnVar;
        }

        /*if (!$queue) {
            list($queue, $command) = explode(" ", $command, 2);
        }*/

        if (!$queue) {
            $queue = 'sparkrebel-main';
        }

        switch ($priority) {
            case 'high':
                $priority = static::HIGH_PRIORITY;
                break;
            case 'low':
                $priority = static::LOW_PRIORITY;
                break;
            default:
                $priority = static::MEDIUM_PRIORITY;
        }
        
        return $this->jobClient
            ->useTube($queue)
            ->put(json_encode($command), $priority);
        //return $this->jobClient->$gearmanMethod($queue, $command, $uniqueId);
    }

    /**
     * publish the occurance of an event to the pub sub system
     *
     * @param string $event   the name of the event to announce
     * @param mixed  $message data to publish with the event, will be json encoded
     *
     * @return result of redis publish, or false if disabled
     */
    public function publishPubSub($event, $message)
    {
        if (!$this->config['mode']) {
            return false;
        }
        if ($this->redis) {
            $message = json_encode($message);
            return $this->redis->publish($event, $message);
        }
    }

    /**
     * postPersist
     *
     * @param LifecycleEventArgs $args some object thing
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $doc = $args->getDocument();
        
        $class = get_class($doc);
        $className = lcfirst(substr($class, strripos($class, '\\') + 1));

        switch ($className) {
            case 'activity':
            case 'event':
                break;
            case 'board':
                if (!$doc->getCreatedBy()) {
                    throw new \Exception('Board listener can\'t publish event: board ' . $doc->getId() . ' has no createdBy user');
                }
                $this->publish(
                    'board.create',
                    array(
                        'boardId' => $doc->getId(),
                        'userId' => $doc->getCreatedBy()->getId(),
                    ),
                    'high',
                    '',
                    'feeds'
                );
                break;
            case 'follow':
                $follower = $doc->getFollower();
                $target = $doc->getTarget();

                if ($target instanceOf User) {
                    $eventType = 'user.follow';
                    $type = $target->getUserType();
                } elseif ($target instanceOf Board) {
                    $eventType = 'board.follow';
                    $type = $target->getCreatedBy()->getUserType();
                } else {
                    throw \Exception("Cannot handle following a " . get_class($target));
                }

                $this->publish(
                    $eventType,
                    array(
                        'followId' => $doc->getId(),
                        'followerId' => $follower->getId(),
                        'targetId' => $target->getId(),
                        'type' => $type
                    ),
                    'high',
                    '',
                    'feeds'
                );
                break;
            case 'post':
                if (!$doc->getCreatedBy()) {
                    throw new \Exception('Post listener can\'t publish event: post ' . $doc->getId() . ' has no createdBy user');
                }

               if($doc->getCreatedBy()->hasRole('ROLE_CURATOR') === false) {
                    $this->publish(
                        'post.create',
                        array(
                            'postId' => $doc->getId(),
                            'userId' => $doc->getCreatedBy()->getId(),
                        ),
                        'high'
                    );

                    $parent = $doc->getParent();
                    if ($parent) {
                        $this->publish(
                            'post.repost',
                            array(
                                'postId' => $doc->getId(),
                                'userId' => $doc->getCreatedBy()->getId(),
                                'parentPostId' => $parent->getId(),
                                'parentUserId' => $parent->getCreatedBy()->getId(),
                            )
                        );
                    }
                }

                
                break;
            case 'postComment':
                if (!$doc->getCreatedBy()) {
                    throw new \Exception('Comment listener can\'t publish event: post ' . $doc->getId() . ' has no createdBy user');
                }

                $post = $doc->getPost();
                if ($post) {
                    $this->publish(
                        'comment.create',
                        array(
                            'commentId' => $doc->getId(),
                            'userId' => $doc->getCreatedBy()->getId(),
                            'postId' => $post->getId(),
                            'postUserId' => $post->getCreatedBy()->getId()
                        ),
                        'high', 
                        '',                       
                        'feeds'
                    );
                } else {
                    $this->publish(
                        'comment.reply',
                        array(
                            'commentId' => $doc->getId(),
                            'userId' => $doc->getCreatedBy()->getId()
                        ),
                        'high',                        
                        '',
                        'feeds'
                    );
                }
                break;
            case 'postActivity':
                if (!$doc->getCreatedBy()) {
                    throw new \Exception('Activity listener can\'t publish event: post ' . $doc->getId() . ' has no createdBy user');
                }

                $post = $doc->getPost();
                if (!$post) {
                    throw new \Exception('Activity Listener can\'t publish event: for activity ' . $doc->    getId() . ' has no post');
                }
                $this->publish(
                    'activity.create',
                    array(
                        'activityId' => $doc->getId(),
                        'userId' => $doc->getCreatedBy()->getId(),
                        'postId' => $post->getId(),
                        'postUserId' => $post->getCreatedBy()->getId()
                    ),
                    'high',
                    '',
                    'feeds'

                );
                break;
            default:
                if (!method_exists($doc, 'getId')) {
                    return;
                }

                $underscoreName = strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $className));

                $message = array($className . 'Id' => $doc->getId());
                if (method_exists($doc, 'getCreatedBy')) {
                    $user = $doc->getCreatedBy();
                    if ($user) {
                        $message['userId'] = $user->getId();
                    }

                    $this->publish($underscoreName . '.create', $message);
                }
        }
    }

    /**
     * postRemove
     *
     * Only called by postUpdate
     *
     * @param LifecycleEventArgs $args some object thing
     */
    public function postRemove(LifecycleEventArgs $args)
    {
        $doc = $args->getDocument();

        $class = get_class($doc);
        $className = lcfirst(substr($class, strripos($class, '\\') + 1));

        if (!method_exists($doc, 'getId')) {
            return;
        };

        switch ($className) {
            case 'activity':
            case 'event':
                break;
            case 'board':
                $message = array(
                    'boardId' => $doc->getId(),
                    'userId' => $doc->getCreatedBy()->getId()
                );
                $this->publish('board.delete', $message, 'high', '', 'feeds');
                break;

            case 'post':
                $message = array(
                    'postId' => $doc->getId(),
                    'boardId' => $doc->getBoard()->getId(),
                    'userId' => $doc->getCreatedBy()->getId()
                );
                $this->publish('post.delete', $message, 'high', '', 'feeds');
                break;

            case 'follow':
                $follower = $doc->getFollower();
                $target = $doc->getTarget();

                if ($target instanceOf User) {
                    $eventType = 'user.unfollow';
                    $type = $target->getUserType();
                } elseif ($target instanceOf Board) {
                    $eventType = 'board.unfollow';
                    $type = $target->getCreatedBy()->getUserType();
                } else {
                    throw \Exception("Cannot handle unfollowing a " . get_class($target));
                }
                
                if ($eventType=='board.unfollow' && ($type=='brand' || $type=='merchant')) {
                    break; //skip stream:refresh [id] brand
                }

                $this->publish(
                    $eventType,
                    array(
                        'followerId' => $follower->getId(),
                        'targetId' => $target->getId(),
                        'type' => $type
                    ),
                    'high'
                );
                break;

            default:
                $underscoreName = strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $className));

                $message = array($className . 'Id' => $doc->getId());
                if (method_exists($doc, 'getCreatedBy')) {
                    $user = $doc->getCreatedBy();
                    if ($user) {
                        $message['userId'] = $user->getId();
                    }
                }

                $this->publish($underscoreName . '.delete', $message);
        }
    }

    /**
     * postUpdate
     *
     * @param LifecycleEventArgs $args some object thing
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $doc = $args->getDocument();

        $class = get_class($doc);
        $className = lcfirst(substr($class, strripos($class, '\\') + 1));

        if (!method_exists($doc, 'getId')) {
            return;
        };

        if (method_exists($doc, 'getDeleted') && $doc->getDeleted()) {
            if (method_exists($doc, 'setIsActive')) {
                $doc->setIsActive(false);
            }

            $this->postRemove($args);
            return;
        }

        switch ($className) {
            case 'activity':
            case 'event':
            case 'post': // TODO when you can edit posts, this should be removed
                break;
            default:
                $underscoreName = strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $className));

                $message = array($className . 'Id' => $doc->getId());
                if (method_exists($doc, 'getCreatedBy')) {
                    $user = $doc->getCreatedBy();
                    if ($user) {
                        $message['userId'] = $user->getId();
                    }
                }

                $this->publish($underscoreName . '.edit', $message);
        }
    }

    /**
     * Explicitly set the execution mode for processing events.
     *
     * @param string $mode foreground or background
     */
    public function setMode($mode)
    {
        $this->config['mode'] = $mode;
    }

    /**
     * getMode
     *
     * @return string
     */
    public function getMode()
    {
        return $this->config['mode'];
    }

    /**
     * getRequests
     *
     * @param string $type of requests to return
     *
     * @return array
     */
    public function getRequests($type = null)
    {
        if (!empty($type)) {
            if (isset($this->requests[$type])) {
                return $this->requests[$type];
            } else {
                return array();
            }
        }
        return $this->requests;
    }

    /**
     * resetRequests
     */
    public function resetRequests()
    {
        $this->requests = array();
    }

    /**
     * setRedis
     *
     * @param mixed $redis instance
     */
    public function setRedis($redis)
    {
        $this->redis = $redis;
    }

    public function setPheanstalk($p)
    {
        $this->pheanstalk = $p;
    }

    /**
     * Log that an event has been triggered
     *
     * Bypass doctrine so that there is no flush
     *
     * @param string $method the name of the method
     * @param string $args   array or args passed
     */
    protected function _log($method, $args)
    {
        $this->requests[$method][] = $args;

        if ($method === 'publish') {
            /*if ($collection = $this->dm->getDocumentCollection('PWApplicationBundle:Event')->getMongoCollection()) {
                $args['created'] = new \MongoDate();
                $collection->insert($args);
            }*/
        }
    }
}
