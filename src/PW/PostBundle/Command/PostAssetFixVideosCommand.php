<?php

namespace PW\PostBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    PW\ApplicationBundle\Command\AbstractCommand;


class PostAssetFixVideosCommand extends AbstractCommand
{

    /**
     * configure
     */
    protected function configure()
    {
        $this
            ->setName('post:asset:fix-all-videos')
            ->setDescription('Fixe all post asset videos from original source')
            ->setDefinition(array(                
            ));
    }

    /**
     *
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $limit = 100;
        $key = 'posts:lastprocessed:asset:video';
        $qb = $this->getContainer()
            ->get('pw_post.post_manager')
            ->getRepository()
            ->createQueryBuilder()
            ->field('delete')->equals(null)
            ->field('isVideoPost')->equals(true)
            ->sort('created', 'desc')
            ->limit($limit);

        $redis = $this->getContainer()->get('snc_redis.default');

        $last_processed = $redis->get($key);    
        if($last_processed) {
            $qb->field('id')->lt($last_processed);
        }


        $posts = $qb->getQuery()->execute();
        
        $last_saved_id = null;    
        foreach ($posts as $post) {
            $this->getEventManager()->requestJob("post:asset:fix {$post->getId()}");
            $last_saved_id = $post->getId();
        }

        $redis->set($key, $last_saved_id);    
                            
    }

}
