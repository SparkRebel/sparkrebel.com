<?php

namespace PW\PicscoutBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    PW\ApplicationBundle\Command\AbstractCommand;


/**
 * Double checks assets from given date
 *
 **/

class FixAssetsCommand extends AbstractCommand
{

    /**
     * configure
     */
    protected function configure()
    {
        $this
            ->setName('pw:picscout:fix')
            ->setDescription('Fix assets for getty')
            ->setDefinition(array(
                new InputArgument(
                    'dateFrom',
                    InputArgument::REQUIRED,
                    'The date from it should be fixed'
                )
            ));
    }

    /**
     *
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $date = $input->getArgument('dateFrom');

        $output->writeln("Checking...");

        $dm = $this->getContainer()->get('doctrine_mongodb.odm.document_manager');
        $qb = $dm->getRepository('PWPostBundle:Post')
            ->createQueryBuilder()
            ->field('createdBy.type')->equals('user')
            ->field('isCeleb')->equals(false)
            ->field('created')->gt(new \MongoDate(strtotime("{$date} 00:00:00")));


        $posts = $qb->getQuery()->execute();
        $tasks  = $this->getEventManager();
        
        $total = 0;
        foreach ($posts as $post) {      
            $output->writeln("<info>Checking post " . $post->getId() . '</info>');
      
            $asset = $post->getImage();
            $tasks->requestJob("pw:picscout:check " . $asset->getId());            
            $total++;
        }
        $output->writeln("Sheldued " . $total . ' assets');


    }

}
