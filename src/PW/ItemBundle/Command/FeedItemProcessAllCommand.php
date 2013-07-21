<?php

namespace PW\ItemBundle\Command;

use PW\ApplicationBundle\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PW\ApplicationBundle\Resources\ProgressBar;

/**
 * A testing/dev only command
 *
 * Register or execute jobs for all feed items
 */
class FeedItemProcessAllCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('feed:item:processAll')
            ->setDescription('For dev/testing. Reprocess all feed items')
            ->addOption(
                'foreground', null, InputOption::VALUE_NONE, 'Run in the foregroud, do not use gearman workers'
            );
    }

    /**
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $foreground   = $input->getOption('foreground');
        $eventManager = $this->getEventManager();
        if ($foreground) {
            $eventManager->setMode('foreground');
        }

        $limit = 1000;
        $port  = $this->getContainer()->getParameter('mongodb.default.port');
        $host  = $this->getContainer()->getParameter('mongodb.default.host');
        $db    = $this->getContainer()->getParameter('mongodb.default.name');

        $m  = new \Mongo("mongodb://$host:$port");
        $db = $m->selectDB($db);

        $feedItemRepo = $db->selectCollection('feed_items');
        $conditions   = array('status' => 'pending');
        $total        = $feedItemRepo->count($conditions);

        echo ProgressBar::start($total, 'Processing feed items');

        while (true) {
            $feedItems = $feedItemRepo
                ->find($conditions)
                ->sort(array('fid' => true))
                ->limit($limit);

            if ($foreground) {
                $i = 1;
                $ids = array();
                foreach ($feedItems as $feedItem) {
                    $ids[$i] = "\n\t$i : " . $feedItem['fid'];
                    $i++;
                }
                $output->write($ids);
                $output->write("\n");
            }

            $id = null;
            foreach ($feedItems as $feedItem) {
                $id = $feedItem['fid'];
                $eventManager->requestJob('feed:item:process ' . escapeshellarg($id), 'low', '', '', 'feeds');
                echo ProgressBar::next();
            }

            if (!$id) {
                break;
            }

            $conditions['fid']['$gt'] = $id;
        }

        echo ProgressBar::finish();

        $output->writeln('');
        $output->writeln("<info>Finished processing {$total} items...</info>");
    }
}
