<?php

namespace PW\GettyImagesBundle\Command\Report;

use PW\ApplicationBundle\Command\AbstractCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PW\GettyImagesBundle\Document\Usage;

class SendCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('getty:report:send')
            ->setDescription('Send usage of the current month to Getty')
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
        
        /* @var $manager \PW\GettyImagesBundle\Model\GettyReportManager */
        $getty_report_manager = $this->getContainer()->get('pw_getty_images.getty_report_manager');
        $getty_report = $getty_report_manager->getNewestOne();
        if (!$getty_report || $getty_report->getStatus()!='ready_to_send') {
            die('GettyReport is not ready to send (or doesnt exists?).');
        }
        $this->updateReportStatus($getty_report, 'sending', 'sending - starting', true);
        
        $rows  = $usage->createQueryBuilder()
            ->distinct('gettyId')
            ->field('gettyId')->gt(0)
            ->field('sent')->equals(0)
            ->getQuery()->execute();
        
        $distinct_gettyIds = array();
        foreach ($rows as $id) {
            $distinct_gettyIds[] = $id;
        }

        $getty  = new \PW\CelebBundle\GettyImage;
        $getty->setDebugOutput(true); //print json requests and responses

        $all_count = count($distinct_gettyIds);
        $count = 0;
        
        foreach ($distinct_gettyIds as $id) {
            $docs = $usage->createQueryBuilder()
                ->field('sent')->equals(0)
                ->field('gettyId')->equals($id)
                ->getQuery()->execute();

            $quantity = 0;
            $ids = array();
            foreach ($docs as $doc) {
                $quantity += $doc->GetQuantity();
                $ids[] = $doc->getId();
            }

            if ($doc->getCreated()) {
                $created_miliseconds = $doc->getCreated()->getTimestamp() * 1000; 
            } else {
                $created_miliseconds = strtotime($doc->getMonth().'-01') * 1000; 
            }
            $UsageDate  = "/Date(" . $created_miliseconds . "-0000)/";
            $data = array('ReportUsageRequestBody' => array(
                'TransactionId' => ''.$doc->getId(),
                'AssetUsages' => array(
                    array(
                        "AssetId"   => ''.$doc->getGettyId(),
                        "Quantity"  => intval($quantity),
                        "UsageDate" => $UsageDate
                    ),
                ),
            ));

            $response = $getty->Query("http://connect.gettyimages.com/v1/usage/ReportUsage", $data);

            $usage->createQueryBuilder()
                ->update()
                ->field('sent')->set(1)
                ->field('_id')->in($ids)
                ->getQuery()
                ->execute();
            $count++;
            if ($count % 10 == 0) {
                $this->updateReportStatus($getty_report, 'sending', 'sending - '.$count.'/'.$all_count, true);
            }
        }
        $this->updateReportStatus($getty_report, 'sent', 'this report was sent', true);
    }
}
