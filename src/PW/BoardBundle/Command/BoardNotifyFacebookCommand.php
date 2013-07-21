<?php

namespace PW\BoardBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    Doctrine\ODM\MongoDB\DocumentNotFoundException;

/**
 * BoardNotifyFacebookCommand - notify facebook about Board
 */
class BoardNotifyFacebookCommand extends ContainerAwareCommand
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
            ->setName('board:notify:facebook')
            ->setDescription('notify Facebook about boards that had been modified so FB can scrape board page')
            ->setDefinition(array(
                new InputArgument('boardId', InputArgument::OPTIONAL, 'The Board $id'),
                new InputOption('startid', '-startid', InputOption::VALUE_OPTIONAL, 'Start from specific board ID', 'no'),
                new InputOption('reset', '-r', InputOption::VALUE_NONE, 'Re-notify all boards')
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
        $this->output->writeln("<info>[" . date('Y-m-d H:i:s') . "] starting command board:notify:facebook</info>");
        
        $input_boardId = $input->getArgument('boardId');
        $reset     = $input->getOption('reset');
        $startId   = $input->getOption('startid');
        if ($startId=='no') $startId = null;
 
        if ($input_boardId) {
            $this->output->writeln("<info>...with boardId: {$input_boardId}</info>");
        }
        if ($startId) {
            $this->output->writeln("<info>...with startid: {$startId}</info>");
        }
  
        $this->dm = $this->getContainer()
            ->get('doctrine_mongodb.odm.document_manager');
        $catRepo = $this->dm->getRepository('PWCategoryBundle:Category');
        $boardRepo = $this->dm->getRepository('PWBoardBundle:Board');

        
        $boards = array();
        if (!$input_boardId) {
            $conditions = array();
            $conditions['isActive'] = true;
            $conditions['postCount']['$gt'] = 0;
            if ($startId) {
                $conditions['_id']['$gte'] = $startId;
            }
            if (!$reset) {
                $conditions['$or'][]['notifiedFbAt'] = null; // take Boards never scraped by Facebook
                $conditions['$or'][]['modified']['$gte'] = new \MongoDate( time()-6*60*60 ); // take Boards updated 6h ago and later
            }
            $boards = $boardRepo->findBy($conditions)->sort(array('_id' => 'asc'));
            $count = $boards->count();
            $this->output->writeln("<info>Found {$count} Board candidates to notify FB</info>");
        } else {
            $board = $boardRepo->find(new \MongoId($input_boardId));
            if ($board) {
                $boards[] = $board;
                $count = 1;
                $this->output->writeln("<info>Found Board {$input_boardId}</info>");
            } else {
                $this->output->writeln("<error>Board {$input_boardId} not found</error>");
            }
        }

        if (empty($boards) || $count<1) {
            return false;
        }

        $router = $this->getContainer()->get('router');
        $router->getContext()->setHost( $this->getContainer()->getParameter('host') );
        
        $countNotified = 0;
        $countSkippedInARow = 0;
        foreach ($boards as $board) {
            if (!$board->getIsActive() || $board->getDeleted() || $board->getPostCount()<1) {
                // not active, deleted or no Posts in Board -> skipping
                $countSkippedInARow++;
                continue;
            }

            $notifiedFbAt = $board->getNotifiedFbAt();
            if (!$reset && $notifiedFbAt) {
                $diff = $board->getModified()->getTimestamp() - $notifiedFbAt->getTimestamp();
                if ($diff < 180) {
                    // facebook was recently notified about this board -> skip
                    $countSkippedInARow++;
                    continue;
                }
            }
            
            if ($countSkippedInARow>0) {
                $this->output->writeln("Skipped: ".$countSkippedInARow);
                $countSkippedInARow = 0;
            }

            try {
                $user = $board->getCreatedBy();
                $userId = $user->getId();
                $user->getName(); // query User to see if it exists            
                
                $this->output->writeln("<info>Notifying FB about Board {$board->getId()}</info>");
                if ($reset) {
                    $this->output->write("<info>resetting </info>");
                } else if ($notifiedFbAt) {
                    $this->output->writeln("<info>board.modified: ".date('Y-m-d H:i:s',$board->getModified()->getTimestamp())
                        ."board.notifiedFbAt: ".date('Y-m-d H:i:s',$notifiedFbAt->getTimestamp())
                        ."</info>");
                } else {
                    $this->output->writeln("<info>board.notifiedFbAt: never</info>");
                }
            
                $boardUrl = $router->generate('pw_board_default_view'
                    , array('id'=>$board->getId(), 'slug'=>$board->getSlug()), true);
                $output->write("$boardUrl \n");
                $c = curl_init();
                curl_setopt($c, CURLOPT_URL, 'https://graph.facebook.com/');
                curl_setopt($c, CURLOPT_POST, 1); // POST
                curl_setopt($c, CURLOPT_POSTFIELDS, 'id='.$boardUrl.'&scrape=true');
                curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
                $result = curl_exec($c);
                curl_close($c);
                if ($input_boardId) {
                    $output->write('result: '.print_r($result,true)." \n");
                }
            } catch (DocumentNotFoundException $e) {
                $this->output->writeln("<error>Author of Board {$board->getId()} not found (User {$userId}) -> skipping</error>");
                // this Board is broken -> set isActive=false
                $this->dm->clear();
                $this->dm->createQueryBuilder('PWBoardBundle:Board')
                    ->update()
                    ->field('isActive')->set(false)
                    ->field('_id')->equals(new \MongoId($board->getId()))
                    ->getQuery()
                    ->execute();
                $this->dm->flush();
                continue;
            }

            $board->setNotifiedFbAt(new \DateTime());
            $this->dm->persist($board);
            $countNotified++;
            if ($countNotified % 1 === 0) {
                $this->dm->flush();
            }
        }

        $this->dm->flush();

        $this->output->write("notified FB about $countNotified boards \n");
        $this->output->writeln("<info>[" . date('Y-m-d H:i:s') . "] ending command board:notify:facebook</info>");
    }

}
