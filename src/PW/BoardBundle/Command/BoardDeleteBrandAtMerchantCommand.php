<?php

namespace PW\BoardBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    Doctrine\ODM\MongoDB\DocumentNotFoundException;

/**
 * BoardDeleteBrandAtMerchantCommand - delete 'brand @ merchant' boards
 */
class BoardDeleteBrandAtMerchantCommand extends ContainerAwareCommand
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
            ->setName('board:delete-brand-at-merchant')
            ->setDescription('notify Facebook about boards that had been modified so FB can scrape board page')
            ->setDefinition(array(
                new InputArgument('boardId', InputArgument::OPTIONAL, 'The Board $id')
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
        $this->output = $output;
        $this->output->writeln("<info>[" . date('Y-m-d H:i:s') . "] starting command board:delete-brand-at-merchant</info>");
        
        $input_boardId = $input->getArgument('boardId');
        if ($input_boardId) {
            $this->output->writeln("<info>...with boardId: {$input_boardId}</info>");
        }
  
        $this->dm = $this->getContainer()
            ->get('doctrine_mongodb.odm.document_manager');
        $boardRepo = $this->dm->getRepository('PWBoardBundle:Board');

        
        $boards = array();
        if (!$input_boardId) {
            $conditions = array();
            $conditions['isActive'] = true;
            $conditions['name'] = new \MongoRegex('/ @ /'); // take only assets with url on s3
            $boards = $boardRepo->findBy($conditions)->sort(array('_id' => 'asc'));
            $count = $boards->count();
            $this->output->writeln("<info>Found {$count} Board candidates to delete</info>");
        } else {
            $board = $boardRepo->find(new \MongoId($input_boardId));
            if ($board) {
                if (strpos($board->getName(),' @ ')===false) {
                    $board = null;
                    $this->output->writeln("<error>Board {$input_boardId} is not 'brand @ merchant' board</error>");
                } else {
                    $boards[] = $board;
                    $count = 1;
                    $this->output->writeln("<info>Found Board {$input_boardId}</info>");
                }
            } else {
                $this->output->writeln("<error>Board {$input_boardId} not found</error>");
            }
        }

        if (empty($boards) || $count<1) {
            return false;
        }

        $boardManager = $this->getContainer()->get('pw_board.board_manager');

        $countDeleted = 0;
        $countSkippedInARow = 0;
        foreach ($boards as $board) {
            if (!$board->getIsActive() || $board->getDeleted()) {
                // not active or deleted -> skipping
                $countSkippedInARow++;
                continue;
            }
            
            if ($countSkippedInARow>0) {
                $this->output->writeln("Skipped: ".$countSkippedInARow);
                $countSkippedInARow = 0;
            }

            $boardManager->delete($board);
            $countDeleted++;
        }

        $this->dm->flush();

        $this->output->write("deleted $countDeleted boards \n");
        $this->output->writeln("<info>[" . date('Y-m-d H:i:s') . "] ending command board:delete-brand-at-merchant</info>");
    }

}
