<?php

namespace PW\AssetBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;

/**
 * Upload all versions of an asset to the s3
 */
class S3ObjectUploadCommand extends ContainerAwareCommand
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
            ->setName('s3:object:upload')
            ->setDescription('Add/overwrite one object to an s3 bucket')
            ->setDefinition(array(
                new InputArgument(
                    'bucket',
                    InputArgument::REQUIRED,
                    'The name of the bucket to process'
                ),
                new InputArgument(
                    'file',
                    InputArgument::REQUIRED,
                    'the file to upload'
                )
            ))->addOption(
                'path',
                null,
                InputOption::VALUE_OPTIONAL,
                'The relative path to use on the s3'
            );
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
        $path = $input->getArgument('file');
        $url = $input->getOption('path');
        if (!$url) {
            $url = preg_replace('@^(web/|/)@', '', $path);
        }
        $url = ltrim($url, '/');

        if ($this->s3->putObjectFile($path, $bucket, $url, \S3::ACL_PUBLIC_READ)) {
            $output->write("http://$bucket/$url uploaded successfully\n");
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
