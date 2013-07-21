<?php

namespace PW\TaggingBundle\Command;

use PW\TaggingBundle\Document\Tagging;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TaggingsPopulateCommand extends ContainerAwareCommand
{
    protected $taggings = array(
        'All things Vintage',
        'Back to Basics',
        'Boho Chic',
        'Classy Girl',
        'Girly Girl',
        'Indie & Edgy',
        'Preppy',
        'Rocker Chic',
        'Things I ❤',
    );

    protected $dm;

    protected function configure()
    {
        $this
            ->setName('taggins:populate')
            ->setDescription('Populate taggings from list')
        ;
    }

    /**
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        $this->dm = $this->getContainer()->get('doctrine_mongodb.odm.document_manager');

   
        foreach ($this->taggings as $tagging) {
            $tag = $this
                ->dm
                ->getRepository('PWTaggingBundle:Tagging')
                ->findOneByName($tagging)
            ;
            if(!$tag) {
                $tag = new Tagging;
                $tag->setName($tagging);                
                $this->dm->persist($tag);
                $this->dm->flush();
                $output->writeln("<info>Adding {$tagging}</info>");
            } else {
                $output->writeln("<info>{$tagging} already exist.</info>");
            }            

        }
    }
    
}
