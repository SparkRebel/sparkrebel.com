<?php

namespace PW\AssetBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;

/**
 * Show the names of all buckets on this configured account
 */
class S3BucketListCommand extends ContainerAwareCommand
{

    /**
     * document manager placeholder
     */
    protected $dm;

    /**
     * configure
     */
    protected function configure()
    {
        $this
            ->setName('s3:bucket:list')
            ->setDescription('List all buckets')
            ->setDefinition(array(
            ));
    }

    /**
     * Execute
     *
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $buckets = $this->s3->listBuckets();

        foreach ($buckets as $bucket) {
            $location = $this->s3->getBucketLocation($bucket);
            $acp = $this->s3->getAccessControlPolicy($bucket);

            $output->write($bucket . " owner: {$acp['owner']['name']}. location: $location\n");
        }
    }

    /**
     * Lazy load/reference any dependent services
     *
     * @param string $field the field of this class
     *
     * @return mixed
     */
    public function __get($field)
    {
        if ($field === 's3') {
            $accessKey = $this->getContainer()->getParameter('s3_access_key');
            $secretKey = $this->getContainer()->getParameter('s3_secret_key');
            return $this->s3 = new \S3($accessKey, $secretKey);
        }
    }
}
