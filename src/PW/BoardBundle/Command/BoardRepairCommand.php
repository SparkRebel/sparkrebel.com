<?php

namespace PW\BoardBundle\Command;

use PW\ApplicationBundle\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PW\BoardBundle\Document\Board;

class BoardRepairCommand extends AbstractCommand
{
    protected function configure()
    {
        $this->setName('repair:boards')
             ->setDescription('Repair boards')
             ->setDefinition(array(
                 new InputOption('type', 't', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Type of repair', array('counts', 'images')),
                 new InputOption('all', null, InputOption::VALUE_NONE, 'Repair only detectable or all?'),
                 new InputOption('celebs', null, InputOption::VALUE_NONE, 'Repair only celebs?'),
                 new InputOption('board_id', null, InputArgument::OPTIONAL, 'Repair only specific board by id'),
             ));
    }

    /**
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $types = $input->getOption('type');
        $this->board_id = $input->getOption('board_id');

        if ($input->getOption('all')) {
            $output->writeln('');
            $output->writeln('Starting repair:boards for: <comment>all</comment>');

            $boards = $this->getBoardManager()->getRepository()
                ->createQueryBuilderWithOptions()
                ->getQuery()->execute();

            $output->writeln('Found <comment>' . $boards->count() . '</comment> boards to repair...');

            foreach ($boards as $board) {
                $board = $this->repairImagesForBoard($board);
                $this->getBoardManager()->processCounts($board);
            }

        } elseif ($input->getOption('celebs')) {
            $output->writeln('');
            $output->writeln('Starting repair:boards for: <comment>celebs</comment>');

            $user = $this->getUserManager()->getRepository()->findOneByName('Celebs');
       
            if (!$user) {
                throw $this->createNotFoundException("Celebs user not found");
            } 

            $boards = $this->getBoardManager()->getRepository()
                ->createQueryBuilder()
                ->field('createdBy')->references($user)
                ->field('isActive')->equals(true)
                ->sort('name', 'asc')
                ->getQuery()->execute();

            $output->writeln('Found <comment>' . $boards->count() . '</comment> boards to repair...');

            foreach ($boards as $board) {
                $board = $this->repairImagesForBoard($board);
                $this->getBoardManager()->processCounts($board);
            }

        } else {
            foreach ($types as $type) {
                switch ($type) {
                    case 'counts':
                        $this->repairCounts($output);
                        break;
                    case 'images':
                        $this->repairImages($output);
                        break;
                    default:
                        break;
                }
            }
        }

        $output->writeln('');
        $output->writeln('<question>Repair finished!</question>');
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    private function repairCounts(OutputInterface $output)
    {
        $output->writeln('');
        $output->writeln('Starting repair:boards for: <comment>counts</comment>');

        $qb = $this->getBoardManager()->getRepository()
            ->createQueryBuilderWithOptions();

        $boards = $qb
            ->addOr($qb->expr()->field('postCount')->equals(0))
            ->addOr($qb->expr()->field('followerCount')->equals(0))
            ->getQuery()->execute();

        $output->writeln('Found <comment>' . $boards->count() . '</comment> boards to repair...');

        foreach ($boards as $board /* @var $board \PW\BoardBundle\Document\Board */) {
            $this->getBoardManager()->processCounts($board);
        }

        $output->writeln('<info>Complete</info>');
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    private function repairImages(OutputInterface $output)
    {
        $output->writeln('');
        $output->writeln('Starting repair:boards for: <comment>images</comment>');

        $boards = array();
        if (!$this->board_id) {
            $qb = $this->getBoardManager()->getRepository()
                ->createQueryBuilderWithOptions();

            $boards = $qb
                ->addOr($qb->expr()->field('images')->exists(false))
                ->addOr($qb->expr()->field('images')->size(0))
                ->addOr($qb->expr()->field('images')->size(1))
                ->addOr($qb->expr()->field('images')->size(2))
                ->addOr($qb->expr()->field('images')->size(3))
                ->getQuery()->execute();
            $count = $boards->count();
        } else {
            $x = $this->getBoardManager()->find($this->board_id);
            if ($x) {
                $boards[] = $x;
            }
            $count = count(boards);
        }

        $output->writeln('Found <comment>' . $count . '</comment> boards to repair...');

        $i = 1;
        foreach ($boards as $board /* @var $board \PW\BoardBundle\Document\Board */) {
            //$output->writeln('repairing board '.$i.'/'.$count.' ' . $board->getId() . ' <comment>' . $board->getName() . '</comment>');
            $board = $this->repairImagesForBoard($board);
            $this->getBoardManager()->save($board, array('validate' => false));
            $i++;
        }

        $output->writeln('<info>Complete</info>');
    }

    /**
     * @param \PW\BoardBundle\Document\Board $board
     * @return \PW\BoardBundle\Document\Board
     */
    private function repairImagesForBoard(Board $board)
    {
        $posts = $this->getPostManager()->getRepository()
            ->createQueryBuilderWithOptions()
            ->eagerCursor(true)
            ->field('board')->references($board)
            ->sort('created', 'desc')
            ->getQuery()->execute();

        foreach ($posts as $post /* @var $post \PW\PostBundle\Document\Post */) {
            if (count($board->getImages()) >= 4) {
                break;
            }
            if ($image = $post->getImage()) {
                $board->addImages($image);
            }
        }

        return $board;
    }
}
