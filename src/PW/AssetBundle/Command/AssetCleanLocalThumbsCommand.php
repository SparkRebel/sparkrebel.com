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
 * AssetCleanLocalThumbsCommand
 *
 */
class AssetCleanLocalThumbsCommand extends ContainerAwareCommand
{

    /**
     * configure
     */
    protected function configure()
    {
        $this
            ->setName('asset:clean-local-thumbs')
            ->setDescription('For all assets that have url on s3 -> delete their local thumbs')
            ->setDefinition(array(
                new InputOption('startid', '-startid', InputOption::VALUE_OPTIONAL, 'Start from specific asset ID', 'no'),
                new InputOption('limit', '-l', InputOption::VALUE_OPTIONAL, 'How many assets to process', 'no'),
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
        $startId   = $input->getOption('startid');
        if ($startId=='no') $startId = null;
        $limit     = $input->getOption('limit');
        if ($limit=='no') $limit = 5000;
        $startDate  = $input->getOption('startDate');

        $port = $this->getContainer()->getParameter('mongodb.default.port');
        $host = $this->getContainer()->getParameter('mongodb.default.host');
        $db = $this->getContainer()->getParameter('mongodb.default.name');

        $m = new \Mongo("mongodb://$host:$port");
        $db = $m->selectDB($db);

        $assetRepo = $db->selectCollection('assets');

        $conditions = array();
        $conditions['url'] = new \MongoRegex('/i.plumwillow/'); // take only assets with url on s3
        if ($startId) {
            $conditions['_id']['$gte'] = new \MongoId($startId);
        }
        if ($startDate) {
            $conditions['created']['$gte'] = new \MongoDate( strtotime($startDate) );
        }

        $total = $assetRepo->count($conditions);
        $errors = 0;
        echo ProgressBar::start($total, "Processing assets. $errors errors");

        $nullOutput = new NullOutput();
        
        $countProcessed = 0;
        $this->sumFilesizes = 0;

        while (true) {
            $assets = $assetRepo
                ->find($conditions)
                ->sort(array('fid' => true))
                ->limit($limit);

            $id = null;
            foreach ($assets as $asset) {
                $url = $asset['url'];
                $id = $asset['_id'];
                $assetId = $id->__toString();
                $output->write("id: ".$assetId.", url: ".$url."\n");
                if ($url[0] === '/') {
                    $output->write("skipping\n");
                    $countProcessed++;
                    echo ProgressBar::next();
                    continue;
                }
                
                // we will delete only assets in web/assets/ directory
                // (no web/images/* etc, because they are hardcoded in templates sometimes)
                $inputFile = dirname(dirname(dirname(dirname(__DIR__)))) . '/web/assets/' . basename($url);
                $this->deleteLocalVersions($inputFile, true, $output);
                
                $countProcessed++;
                echo ProgressBar::next();
            }
            
            $output->write("sumFilesizes: ".$this->sumFilesizes."\n");
            if (!$id) {
                break;
            }

            unset($conditions['_id']['$gte']);
            $conditions['_id']['$gt'] = $id;
        }

        echo ProgressBar::finish();
        $output->write("<info>Processed ".$countProcessed." assets, sum of filesizes: ".$this->sumFilesizes."</info>\n");
    }
    
    
    protected function deleteLocalVersions($inputFile, $allOfThem = false, $output)
    {
        $pattern = preg_replace('@\.[^\.]+$@', '.*', $inputFile);
        $files = glob($pattern);
        foreach ($files as $file) {
            if (!$allOfThem && $file === $inputFile) {
                $output->write("<info>Keeping $file</info>\n");
                continue;
            }
            $output->write("<info>Deleting $file</info>\n");
            $this->sumFilesizes += filesize($file);
            unlink($file);
        }
    }
}