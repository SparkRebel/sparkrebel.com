<?php

namespace PW\AssetBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;

/**
 * Delete one object (file) from the s3
 *
 * An object is not an asset - it is an asset-version
 */
class S3ObjectDeleteCommand extends ContainerAwareCommand
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
            ->setName('s3:object:delete')
            ->setDescription('Delete one object from an s3 bucket')
            ->setDefinition(array(
                new InputArgument(
                    'bucket',
                    InputArgument::REQUIRED,
                    'The name of the bucket to process'
                ),
                new InputArgument(
                    'object',
                    InputArgument::OPTIONAL,
                    'the relative url for the asset to delete'
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
        $object = $input->getArgument('object');

        if ($this->s3->deleteObject($bucket, $object)) {
            $output->write("http://$bucket/$object deleted successfully\n");
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
