<?php

namespace PW\GettyImagesBundle\Command\Report;

use PW\ApplicationBundle\Resources\ProgressBar;
use PW\ApplicationBundle\Command\AbstractCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PW\GettyImagesBundle\Document\Usage;

class GenerateCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('getty:report:generate')
            ->setDescription('Send usage of the current month to Getty')
            ->setDefinition(array(
                new InputArgument('month', InputArgument::OPTIONAL, 'Month to Generate')
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
        $dm    = $this->getContainer()->get('doctrine_mongodb.odm.document_manager');
        $this->dm = $dm;
        $dbstats = $dm->getRepository('PWStatsBundle:Summary'); 
        $dbusage = $dm->getRepository('PWGettyImagesBundle:Usage'); 
        $month = date('Y-m');
        
        /* @var $manager \PW\GettyImagesBundle\Model\GettyReportManager */
        $getty_report_manager = $this->getContainer()->get('pw_getty_images.getty_report_manager');
        $getty_report = $getty_report_manager->getNewestOne();
        if (!$getty_report || $getty_report->getStatus()!='new') {
            die('Some other GettyReport is in progress probably - please wait until it finishes and then please create new one.');
        }
        $this->updateReportStatus($getty_report, 'generating', 'generating - starting', true);

        $data  = $dbstats->createQueryBuilder()
            ->field('processed')->equals(null)
            ->getQuery()->execute();

        $totals = array();
        $assets = array();

        echo progressbar::start(count($data)+1, 'reading stats');
        $this->updateReportStatus($getty_report, 'generating', 'generating - reading stats', true);

        foreach ($data as $row) {
            echo ProgressBar::next();
            try {
                $parts = explode(":", $row->getId());
                $type  = $parts[1];
                if (strlen($parts[0]) !== 7) {
                    continue; // we only care about montly stats
                }


                $asset = $row->getReference();
                $class = substr(get_class($asset), -5);
                if ($class !== 'Asset' || !$asset->isGetty()) {
                    continue;
                }
                $id = substr($asset->getSource(), 6);
                if (empty($id)) {
                    $meta = $asset->getGettyData();
                    if (empty($meta['imageId'])) continue;
                    $id = $meta['imageId'];
                }

                if (empty($totals[$id])) { 
                    $totals[$id] = array();
                }
                if (empty($totals[$id][$type])) {
                    $totals[$id][$type] = 0;
                }
                $totals[$id][$type] += $row->getTotal();
                $assets[$id]  = $asset;
                $ids[] = $row->getId();
            } catch (\Exception $e) { }
        }
        echo ProgressBar::finish();

        echo progressbar::start(count($totals)+1, 'savind docs');
        $this->updateReportStatus($getty_report, 'generating', 'generating - savind docs', true);
        
        $threshold = 1;
        foreach ($totals as $id => $details) {
            echo ProgressBar::next();
            $doc = current(iterator_to_array($dbusage->createQueryBuilder()
                ->field('gettyId')->equals($id)
                ->field('tobeSend')->equals(0)
                ->field('sent')->equals(0)
                ->limit(1)
                ->getQuery()
                ->execute()));

            if (!empty($doc)) {
                $details['carry'] = $doc->getQuantity();
                $doc->setSent(1);
                $dm->persist($doc);
            }


            $total = array_sum($details);
            $sent  = $total > $threshold;

            if ($sent) {
                $usage = new Usage;
                $usage->setCreated(new \DateTime());
                $usage->setGettyId($id);
                $usage->setQuantity(intval($total/$threshold) * $threshold);
                $usage->setDetails($details);
                $usage->setAsset($assets[$id]);
                $usage->setDate(time());
                $usage->setToBeSent(1);
                $usage->setMonth($month);          
                $dm->persist($usage);
            }

            if ($total % $threshold == 0) {
                continue;
            }

            $usage = new Usage;
            $usage->setCreated(new \DateTime());
            $usage->setGettyId($id);
            $usage->setQuantity($total % $threshold);
            $usage->setAsset($assets[$id]);
            $usage->setDate(time());
            if (!$sent) {
                $usage->setDetails($details);
            }   
            $usage->setMonth($month);
            $dm->persist($usage);
        }
        echo ProgressBar::finish();
        $dm->flush();

        $totals = array_chunk($ids, 100);
        echo progressbar::start(count($totals)+1, 'updating records');
        $this->updateReportStatus($getty_report, 'generating', 'generating - updating records', true);
        foreach ($totals as $ids) {
            $dbstats->createQueryBuilder()
                ->update()->multiple()
                ->field('processed')->set(true)
                ->field('_id')->in($ids)
                ->getQuery()->execute();
            echo ProgressBar::next();
        }
        $dm->flush();
        
        $dir = '/var/www/sparkrebel.com/current';
        $command = "cd $dir && php app/console leezy:pheanstalk:put assets '\"getty:report:preview:csv --env=prod\"' high 0 0 primary --env=prod";
        system($command);
        $this->updateReportStatus($getty_report, 'generated', 'generated - preview creation queued', true);
    }
}
