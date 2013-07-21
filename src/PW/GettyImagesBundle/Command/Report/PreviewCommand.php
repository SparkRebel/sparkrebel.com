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

class PreviewCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('getty:report:preview')
            ->setDescription('Generate preview of the report')
            ->setDefinition(array(
            ));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dm    = $this->getcontainer()->get('doctrine_mongodb.odm.document_manager');
        $usage = $dm->getRepository('PWGettyImagesBundle:Usage');
        $stats = $dm->getRepository('PWStatsBundle:Summary');


        $table = iterator_to_array($usage->findBy(array('sent' => 0)));


        /** sort */
        usort($table, function($a, $b) {
            return $b->getQuantity() - $a->getQuantity();
        });

        $columns = array(
            'image' => '', 
            'gettyId' => 'Getty ID', 
            'quantity' => 'Total', 
            'send?' => 'Send', 
            'view' => 'View', 
            'comment' => 'Comments', 
            'repost' => 'Resparks', 
            'carry' => 'Carried from prev Month',
            'will_carry' => 'Carry for next month',
        );
        @mkdir("/tmp/getty");
        $chunks = array_chunk($table, 100);
        $links  = "<p>" . implode(" ", array_map(function($e) {
            return "<a href='{$e}.html'>$e</a>";
        }, range(1, count($chunks)))) . "</p>";

        foreach ($chunks as $pageid => $content) {
            ob_start();
            $pageid++;
            echo "<h1>Getty Report - " . date("Y-m") . "</h1>";
            echo "<table cellpadding='10' cellspacing='2'>";
            echo "<tr>";
            foreach ($columns as $column => $title) {
                echo "<th>{$title}</th>";
            }
            echo "</tr>";
            
            $colors = array('white','gray');
            foreach ($content as $id=>$row) {
                echo "<tr bgcolor='" . $colors[$id%count($colors)] . "'>";
                $data = $row->getDetails();
                foreach ($columns as $column => $t) {
                    echo "<td align=center valign=center>";
                    switch ($column) {
                    case 'image':
                        echo "<img src='" . $row->getAsset()->getUrl(). "' width=100>";
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
                    default:
                        echo !empty($data[$column]) ? $data[$column] : '0';
                    }
                    echo "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
            echo $links;

            file_put_contents("/tmp/getty/{$pageid}.html", ob_get_clean());
        }
        $output->writeln("<info>Generated report in /tmp/getty</info>");
    
    }
}
