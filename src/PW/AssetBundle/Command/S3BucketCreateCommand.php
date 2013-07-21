<?php

namespace PW\AssetBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;

/**
 * Create a new bucket on s3
 *
 * This does nothing about dns resolution etc - any buckets created here are invisible to the
 * public.
 */
class S3BucketCreateCommand extends ContainerAwareCommand
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
            ->setName('s3:bucket:create')
            ->setDescription('Create a bucket')
            ->setDefinition(array(
                new InputArgument(
                    'bucket',
                    InputArgument::REQUIRED,
                    'The name of the bucket to process'
                )
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
        $bucket = $input->getArgument('bucket');

        if ($this->s3->putBucket($bucket, \S3::ACL_PRIVATE)) {
            $output->write("$bucket created successfully\n");
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
