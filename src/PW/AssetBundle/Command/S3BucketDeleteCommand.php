<?php

namespace PW\AssetBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;

/**
 * Handle processing uploads in the background
 */
class S3BucketDeleteCommand extends ContainerAwareCommand
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
            ->setName('s3:bucket:delete')
            ->setDescription('Delete a bucket')
            ->setDefinition(array(
                new InputArgument(
                    'bucket',
                    InputArgument::REQUIRED,
                    'The name of the bucket to delete'
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

        if ($bucket !== 'unit.tests') {
            throw new \Exception(
                "This command can only process the unit.tests bucket. " .
                "You need to edit the code to override this"
            );
        }

        try {
            $this->s3->deleteBucket($bucket);
            $output->write("$bucket deleted successfully\n");
        } catch (\Exception $e) {
            $message = $e->getMessage();
            if (!strpos($message, 'BucketNotEmpty')) {
                throw $e;
            }
            $output->write("$bucket is not empty, Empty the bucket first.\n");
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
