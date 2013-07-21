<?php

namespace PW\PostBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;


class PostFixVideoAssetsWithNoCodeCommand extends ContainerAwareCommand
{
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
            ->setName('post:asset:fix-no-videocode')
            ->setDescription('Fixes video post assets for asset that are videos but have no code')
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
        $this->dm = $this->getContainer()->get('doctrine_mongodb.odm.document_manager');

        $results = $this->getContainer()
            ->get('pw_post.post_manager')
            ->getRepository()
            ->createQueryBuilder()
            ->field('deleted')->equals(null)
            ->field('isVideoPost')->equals(true)
            ->prime('image')
            ->sort('created', 'desc')
            ->getQuery()->execute();

        
        if (count($results) === 0 ) {
            $output->writeln('<error>No video posts to process</error>');
            return;
        }

        foreach ($results as $video_post) {
            $image = $video_post->getImage();
            if(!$image->getVideoCode()) {
                $sparker = new \PW\PostBundle\Model\VideoSparker($video_post->getLink());
                $image->setVideoCode($sparker->getVideoCode());
                $image->setHost($sparker->getOriginalHost());
                $this->dm->persist($image);
                $output->writeln(sprintf('<info>Processed post %s</info>', $video_post->getId()));
            }
        }                

        $this->dm->flush();      


    }

}
