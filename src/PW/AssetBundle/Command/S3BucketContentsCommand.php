<?php

namespace PW\AssetBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;

/**
 * List the contents of a bucket
 */
class S3BucketContentsCommand extends ContainerAwareCommand
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
            ->setName('s3:bucket:contents')
            ->setDescription('List the contents of a bucket')
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
            ))->addOption(
                'marker',
                null,
                InputOption::VALUE_OPTIONAL,
                'Only results alphabetically after this result will be returned. Used for paging'
            )->addOption(
                'limit',
                null,
                InputOption::VALUE_OPTIONAL,
                'Max number of objects to return',
                100
            )->addOption(
                'delimiter',
                null,
                InputOption::VALUE_OPTIONAL,
                'no idea'
            )->addOption(
                'common-prefixes',
                null,
                InputOption::VALUE_NONE,
                'no idea'
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
        $prefix = $input->getArgument('prefix');

        if ($pos = strpos($bucket, '/')) {
            $prefix = substr($bucket, $pos + 1);
            $bucket = substr($bucket, 0, $pos);
        }

        $marker = $input->getOption('marker');
        $limit = $input->getOption('limit');
        $delimiter = $input->getOption('delimiter');
        $commonPrefixes = $input->getOption('common-prefixes');

        $contents = $this->s3->getBucket($bucket, $prefix, $marker, $limit, $delimiter, $commonPrefixes);

        $i = 0;
        $divider = '|--------|' .
            '----------------|' .
            '--------|' .
            '---------------------------------------------------------------------------|' . "\n";
        $header = ' Count    ' .
            'Date             ' .
            'Kb       ' .
            'Name' . "\n";

        $output->write($divider);
        $output->write($header);
        $output->write($divider);
        foreach ($contents as $row) {
            $i++;
            $output->write(
                ' ' .
                str_pad($i, 8, '0', STR_PAD_LEFT) . ' ' .
                date('Y-m-d H:i', $row['time']) . ' ' .
                str_pad((int) ($row['size']/1024), 8, '0', STR_PAD_LEFT) . ' ' .
                $row['name'] . "\n"
            );
            if ($i % 100 === 0) {
                $output->write($divider);
                $output->write($header);
                $output->write($divider);
            }
        }
        if ($i % 100) {
            $output->write($divider);
            $output->write($header);
            $output->write($divider);
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
