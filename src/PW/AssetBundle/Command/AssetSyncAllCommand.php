<?php

namespace PW\AssetBundle\Command;

use PW\ApplicationBundle\Resources\ProgressBar,
    Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\ArrayInput,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\NullOutput,
    Symfony\Component\Console\Output\OutputInterface;

/**
 * AssetSyncAllCommand
 *
 */
class AssetSyncAllCommand extends ContainerAwareCommand
{

    /**
     * configure
     */
    protected function configure()
    {
        $this
            ->setName('asset:sync:all')
            ->setDescription('Sync assets on the s3')
            ->setDefinition(array(
                new InputOption('mode', '-mode', InputOption::VALUE_OPTIONAL, 'Which mode to run in, choices are "all" or "local"', 'local'),
                new InputOption('startDate', '-startDate', InputOption::VALUE_OPTIONAL, 'Take assets created after given date (please use format: Y-m-d H:i:s)', null)
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
        $mode  = $input->getOption('mode');
        $startDate  = $input->getOption('startDate');
        $output->writeln("<info>[" . date('Y-m-d H:i:s') . "] starting command asset:sync:all --mode=".$mode." --startDate=".$startDate."</info>");
        
        $limit = 100;

        $port = $this->getContainer()->getParameter('mongodb.default.port');
        $host = $this->getContainer()->getParameter('mongodb.default.host');
        $db = $this->getContainer()->getParameter('mongodb.default.name');

        $m = new \Mongo("mongodb://$host:$port");
        $db = $m->selectDB($db);

        $assetRepo = $db->selectCollection('assets');

        $conditions = array(
            '$or' => array(
                array('url' => new \MongoRegex('/^\//')),
                array('thumbsExtension' => array('$nin' => array('jpg','JPG')), 'allowPng' => array('$ne' => true))
            )
        );

        if ($mode === 'all') {
            $conditions = array();
        }
        
        if ($startDate) {
            $conditions['created']['$gte'] = new \MongoDate( strtotime($startDate) );
        }
        
        $total = $assetRepo->count($conditions);
        $errors = 0;
        echo ProgressBar::start($total, "Processing $mode assets. $errors errors");

        $command = $this->getApplication()->find('asset:sync');
        $arguments = array(
            'command' => 'asset:sync',
        );
        $nullOutput = new NullOutput();

        $feelLikeDoingThingsTheSymfonyWay = false;

        while (true) {
            $assets = $assetRepo
                ->find($conditions)
                ->sort(array('fid' => true))
                ->limit($limit);

            $id = null;
            foreach ($assets as $asset) {
                $id = $asset['_id'];
                $assetId = $id;
                if (is_object($assetId)) {
                    $assetId = $id->__toString();
                }
                if ($feelLikeDoingThingsTheSymfonyWay) {
                    $arguments['assetId'] = $id->__toString();
                    $input = new ArrayInput($arguments);
                    try{
                        $returnCode = $command->run($input, $output);
                    } catch(\Exception $e) {
                        d($id);
                        d($e->getMessage());
                        `read foo`;
                        $errors++;
                        ProgressBar::setMessage("Processing $mode assets. $errors errors");
                    }
                } else {
                    $command = "php app/console asset:sync {$assetId} --verbose --env=prod";
                    $output->writeln("<info>asset:sync:all -> sync asset url: ".$asset['url'].", thumbsExtension: ".@$asset['thumbsExtension']."</info>"); 
                    $output->write($command . "\n");
                    $output->write('hash: '. $asset['hash'] . "\n");
                    passthru($command);
                }
                echo ProgressBar::next();
            }

            if (!$id) {
                break;
            }

            $conditions['_id']['$gt'] = $id;
        }

        echo ProgressBar::finish();
        $output->writeln("<info>[" . date('Y-m-d H:i:s') . "] ending command asset:sync:all --mode=".$mode." --startDate=".$startDate."</info>");
    }
}
