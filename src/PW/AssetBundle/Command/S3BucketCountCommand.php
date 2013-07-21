<?php

namespace PW\AssetBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;

/**
 * Count how many entries are in a bucket
 */
class S3BucketCountCommand extends ContainerAwareCommand
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
            ->setName('s3:bucket:count')
            ->setDescription('Count how many items are in a bucket')
            ->setDefinition(array(
                new InputArgument(
                    'bucket',
                    InputArgument::REQUIRED,
                    'The name of the bucket to process'
                ),
                new InputArgument(
                    'prefix',
                    InputArgument::OPTIONAL,
                    'Only return files starting with this string'
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
        $prefix = $input->getArgument('prefix');

        if ($pos = strpos($bucket, '/')) {
            $prefix = substr($bucket, $pos + 1);
            $bucket = substr($bucket, 0, $pos);
        }

        $runningTotal = 0;
        $finished = false;
        $limit = 1000;
        $marker = null;

        while (!$finished) {
            $errorCounter = 0;
            while ($errorCounter < 5) {
                try {
                    $contents = $this->s3->getBucket($bucket, $prefix, $marker, $limit);
                    break;
                } catch (Exception $e) {
                    $output->write('x');
                    $errorCounter += 1;
                }
            }
            if (!$contents) {
                $output->write(' Dead');
                $finished = true;
                break;
            }
            $count = count($contents);
            if ($count < $limit) {
                $finished = true;
                $runningTotal += $count;
                break;
            }

            $end = end($contents);
            $marker = $end['name'];
            $output->write(str_repeat('.', $limit / 1000));
        }

        $output->write("\n$runningTotal items in $bucket\n");
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
