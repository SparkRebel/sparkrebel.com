<?php

namespace PW\CelebBundle\Command;

use PW\UserBundle\Document\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CelebsPopulateCommand extends ContainerAwareCommand
{
    protected $celebs = array(
    	'Kim Kardashian',
		'Rihanna',
		'Angelina Jolie',
		'Lady Gaga',
		'Gisele Bundchen',
		'Jennifer Aniston',
		'Nicole Richie',
		'Kate Middleton',
		'Victoria Beckham',
		'Adriana Lima',
		'Alicia Keys',
		'Alicia Silverstone',
		'Amy Smart',
		'Angelina Jolie',
		'Angie Harmon',
		'Ashlee Simpson',
		'Ashley Judd',
		'Ashley Olsen',
		'Bar Rafaeli',
		'Britney spears',
		'Carrie Underwood',
		'Charlize Theron',
		'Courtney Cox',
		'Demi Moore',
		'Drew Barrymore',
		'Eva Longoria',
		'Gisele Bundchen',
		'Gwen Stefani',
		'Gwenyth Paltrow',
		'Halle berry',
		'Jada Pinkett-Smith',
		'Jaime Pressley',
		'Jennifer Aniston',
		'Jennifer Lopez',
		'Jessica Simpson',
		'Katy Perry',
		'Kendall Jenner',
		'Khloe Kardashian Odom',
		'Kim Kardashian',
		'Kourtney Kardashian',
		'Kylie Jenner',
		'Lady Gaga',
		'Madonna',
		'Mary Kate Olsen',
		'Mena Suvari',
		'Michelle Pfeiffer',
		'Naomi Campbell',
		'Natalie Portman',
		'Nelly Furtado',
		'Nicole Kidman',
		'Penelope Cruz',
		'Rihanna',
		'Scarlett Johansson',
		'Selena Gomez',
		'Taylor Swift',
		'Tyra Banks'
    );

    protected function configure()
    {
        $this
            ->setName('celeb:seed:collections')
            ->setDescription('Populate celebs from list')
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
        $this->repos['users'] = $this->dm->getRepository('PWUserBundle:User');

        $celebs_users = $this->dm->getRepository('PWUserBundle:User')
            ->findOneByName('Celebs');
        if($celebs_users === null) {
            $output->writeln("<error>Celebs user does not exist. Pls run celeb:create:user first</error>");
        } else {
        	foreach ($this->celebs as $celeb) {
        		$board = $this->dm->getRepository('PWBoardBundle:Board')
            		->findOneByName($celeb);
            	if(!$board) {
            		$board = new \PW\BoardBundle\Document\Board;
            		$board->setName($celeb);
                	$board->setCreatedBy($celebs_users);
                	$this->getContainer()->get('pw_board.board_manager')->save($board);
                	$output->writeln("<info>Adding {$celeb}.</info>");
            	} else {
            		$output->writeln("<info>{$celeb} already exist.</info>");
            	}
            	$output->writeln("<info>Adding icon for {$celeb}.</info>");
            	try {
            		$asset = $this->getContainer()->get('pw.asset')->addImage('/images/celebs_icons/'.strtolower($celeb) . '.JPG');
                	$board->setIcon($asset);
                	$this->getContainer()->get('pw_board.board_manager')->save($board);
                	$output->writeln("<info>Icon added.</info>");

            	} catch (\Exception $e) {
            		$output->writeln("<error>Couldnt add icon: \n{$e->getMessage()}.</error>");
            	}
        	}
        }
    }
}
