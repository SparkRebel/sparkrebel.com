<?php

namespace PW\AssetBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    PW\ApplicationBundle\Command\AbstractCommand;


class AssetFixFeedsCommand extends AbstractCommand
{

    /**
     * configure
     */
    protected function configure()
    {
        $this
            ->setName('asset:fix-all')
            ->setDescription('Fixe all asset from original source')
            ->setDefinition(array(
            ));
    }

    /**
     *
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dm = $this->getContainer()->get('doctrine_mongodb.odm.document_manager');
        $qb = $dm->getRepository('PWAssetBundle:Asset')
            ->createQueryBuilder()
            ->field('delete')->equals(null)
            ->field('url')->equals(new \MongoRegex('/^\//'))
            ->field('created')->gt(new \MongoDate(strtotime("2012-08-15 00:00:00")));

       	$assets = $qb->getQuery()->execute();
        $tasks  = $this->getEventManager();


        $i=0;
        foreach ($assets as $asset) {
            $ch = curl_init();
            curl_setopt_array($ch,array(
                CURLOPT_URL => "http://sparkrebel.com" . $asset->getUrl(),
                CURLOPT_HEADER => 1,
                CURLOPT_NOBODY => 1,
                CURLOPT_FOLLOWLOCATION => 1,
                CURLOPT_RETURNTRANSFER => 1,
            ));
            curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($code !== 404) continue;

            $posts = $dm->getRepository('PWPostBundle:Post')
                ->createQueryBuilder()
                ->field('image.$id')->equals(new \MongoId($asset->getId()))
                ->getQuery()->execute();

            foreach ($posts as $post) {
                //$this->getEventManager()->requestJob("post:asset:fix {$post->getId()}", "high");
                $output->writeLn("<info>http://sparkrebel.com/spark/{$post->getId()}</info>");
            }
            if (count($posts) > 0) {
                $tasks->requestJob("asset:sync {$asset->getId()}", "low" , "", "fix:asset:" . $asset->getId(), 'feeds');
            }
        }

    }

}
