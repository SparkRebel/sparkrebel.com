<?php

namespace PW\AssetBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;

/**
 * Clear out the contents of a buckets.
 *
 * Hard coded to only work with the unit tests bucket since running this accidentally on our real
 * buckets would be catestrophic
 */
class S3BucketEmptyCommand extends ContainerAwareCommand
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
            ->setName('s3:bucket:empty')
            ->setDescription('Empty a bucket')
            ->setDefinition(array(
                new InputArgument(
                    'bucket',
                    InputArgument::REQUIRED,
                    'The name of the bucket to empty'
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

        $marker = null;
        $limit = 10;

        $output->write("Requesting $limit items to process\n");
        while ($contents = $this->s3->getBucket($bucket, null, $marker, $limit)) {
            $object = null;

            foreach ($contents as $row) {
                $object = $row['name'];
                try {
                    $this->s3->deleteObject($bucket, $object);
                    $output->write("http://$bucket/$object deleted successfully\n");
                } catch (\Exception $e) {
                    $output->write("Error deleting http://$bucket/$object\n");
                }

            }

            if (!$object) {
                break;
            }
            $marker = $object;
            $output->write("Requesting $limit items to process\n");
        }
        $output->write("Done!");
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
