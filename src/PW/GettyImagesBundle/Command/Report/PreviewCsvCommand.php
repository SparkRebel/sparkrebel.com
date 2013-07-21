<?php

namespace PW\GettyImagesBundle\Command\Report;

use PW\ApplicationBundle\Command\AbstractCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PW\ApplicationBundle\Resources\ProgressBar;
use PW\GettyImagesBundle\Document\Usage;

class PreviewCsvCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('getty:report:preview:csv')
            ->setDescription('Generate preview of the report')
            ->setDefinition(array(
            ));
    }
    
    protected function updateReportStatus($getty_report, $status, $textStatus, $flush = false)
    {
        $getty_report->setStatus($status);
        $getty_report->setTextStatus($textStatus);
        $this->dm->persist($getty_report);
        if ($flush) {
            $this->dm->flush();
        }
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dm    = $this->getcontainer()->get('doctrine_mongodb.odm.document_manager');
        $this->dm = $dm;
        $usage = $dm->getRepository('PWGettyImagesBundle:Usage');
        $stats = $dm->getRepository('PWStatsBundle:Summary');
        
        /* @var $manager \PW\GettyImagesBundle\Model\GettyReportManager */
        $getty_report_manager = $this->getContainer()->get('pw_getty_images.getty_report_manager');
        $getty_report = $getty_report_manager->getNewestOne();
        if (!$getty_report || $getty_report->getStatus()!='generated') {
            die('GettyReport is not ready to create preview (or doesnt exists?).');
        }
        $this->updateReportStatus($getty_report, 'creating_preview', 'creating_preview - starting', true);


        $table = iterator_to_array($usage->findBy(array('sent' => 0)));

        $columns = array(
            'image' => 'Image', 
            'gettyId' => 'Getty ID', 
            'quantity' => 'Total', 
            'send?' => 'Send', 
            'view' => 'View', 
            'comment' => 'Comments', 
            'repost' => 'Resparks', 
            'carry' => 'Carried from prev Month',
            'will_carry' => 'Carry for next month',
            'scoutpic' => 'From PicScout?'
        );

        ob_start();
        foreach ($columns as $column => $title) {
            echo '"' . $title . '";';
        }
        echo "\n";
            
        foreach ($table as $row) {
            $data = $row->getDetails();
            if (!$row->getGettyId()) continue;
            foreach ($columns as $column => $t) {
                ob_start();
                switch ($column) {
                case 'image':
                    echo $row->getAsset()->getUrl();
                    break;
                case 'gettyId':
                    echo $row->getGettyId();
                    break;
                case 'quantity':
                    echo $row->getQuantity();
                    break;
                case 'send?':
                    echo $row->getTobeSend() ? $row->getQuantity() : 0;
                    break;
                case 'will_carry':
                    echo !$row->getTobeSend() ? $row->getQuantity() : 0;
                    break;
                case 'scoutpic':
                    $data = $row->getAsset()->getGettyData();
                    echo !empty($data) && !empty($data['picScoutImageId']) ? 1 : 0;
                    break;
                default:
                    echo !empty($data[$column]) ? $data[$column] : '0';
                }
                $value = ob_get_clean();
                if (is_numeric($value)) {
                    echo "$value;";
                } else {
                    echo '"' . $value . '";';
                }
            }
            echo "\n";
        }

        $path = '/tmp/getty_report_preview_'.$getty_report->getId().'.csv';
        file_put_contents($path, ob_get_clean());
        $output->writeln("<info>Generated report in ".$path."</info>");
        
        $this->updateReportStatus($getty_report, 'ready_to_send', 'ready_to_send - You can look at preview and click "Send" button to send', true);
    }
}
