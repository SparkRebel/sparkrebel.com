<?php

namespace PW\SearchBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\Console\Output\Output;

/**
 * Populate ElasticSearch with custom data sources
 */
class ElasticPopulateCommand extends ContainerAwareCommand
{
    /**
     * @var FOQ\ElasticaBundle\IndexManager
     */
    private $indexManager;

    /**
     * @var FOQ\ElasticaBundle\Provider\ProviderRegistry
     */
    private $providerRegistry;

    /**
     * @var FOQ\ElasticaBundle\Resetter
     */
    private $resetter;

    /**
     * document manager placeholder instance
     */
    protected $dm;

    /**
     * configure
     */
    protected function configure()
    {
        $this
            ->setName('elastic:populate')
            ->setDescription('Populate ElasticSearch with custom data sources')
            ->addOption('reset', null, InputOption::VALUE_NONE, 'If set, the indexes will be resetted before populating and the lockers will be resetted too.')
            ->addOption('index', null, InputOption::VALUE_OPTIONAL, 'The index to repopulate')
            ->addOption('type', null, InputOption::VALUE_OPTIONAL, 'The type to repopulate')
            ->addOption('start', null, InputOption::VALUE_OPTIONAL, 'The start date', '-1 month');
    }

    /**
     * @see Symfony\Component\Console\Command\Command::initialize()
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->indexManager = $this->getContainer()->get('foq_elastica.index_manager');
        $this->providerRegistry = $this->getContainer()->get('foq_elastica.provider_registry');
        $this->resetter = $this->getContainer()->get('foq_elastica.resetter');
    }

    /**
     * @see Symfony\Component\Console\Command\Command::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $index  = $input->getOption('index');
        $type   = $input->getOption('type');
        $start  = new \DateTime($input->getOption('start'));
        $reset  = $input->getOption('reset') ? true : false;

        if(!$reset) {
            $output->writeln('<info>All old documents will be replaced with new ones.</info>');
        }
        $output->writeln('Reindexing data from date: '.$start->format('Y-m-d H:i:s'));

        if (null === $index && null !== $type) {
            throw new \InvalidArgumentException('Cannot specify type option without an index.');
        }

        if (null !== $index) {
            if (null !== $type) {
                $this->populateIndexType($output, $start, $index, $type, $reset);
            } else {
                $this->populateIndex($output, $start, $index, $reset);
            }
        } else {
            $indexes = array_keys($this->indexManager->getAllIndexes());

            foreach ($indexes as $index) {
                $this->populateIndex($output, $start, $index, $reset);
            }
        }
    }

    /**
     * @param $index
     * @param $type
     * @return object
     */
    public function getSearchProvider($index, $type)
    {
        return $this->getContainer()->get('pw.search_provider.'.$index.'.'.$type, false);
    }

    /**
     * @param $index
     * @param $type
     * @return string
     */
    public function getLockerId($index, $type)
    {
        return 'locker_'.$index.'_'.$type;
    }

    /**
     * Recreates an index, populates its types, and refreshes the index.
     *
     * @param OutputInterface $output
     * @param string          $index
     * @param boolean         $reset
     */
    private function populateIndex(OutputInterface $output, $start, $index, $reset)
    {
        if ($reset) {
            $output->writeln(sprintf('<info>Resetting</info> <comment>%s</comment>', $index));
            $this->resetter->resetIndex($index);
        }

        $providers = $this->providerRegistry->getIndexProviders($index);

        foreach ($providers as $type => $provider) {
            $loggerClosure = function($message) use ($output, $index, $type) {
                $output->writeln(sprintf('<info>Populating</info> %s/%s, %s', $index, $type, $message));
            };

            if($provider = $this->getSearchProvider($index, $type)) {
                $output->writeln('Lockers file: '.$provider->getLockersFilePath());

                $lockerId = $this->getLockerId($index, $type);

                if($reset) {
                    $provider->removeLocker($lockerId);
                }

                $provider->populate($start, $loggerClosure, $lockerId);
            }
        }

        $output->writeln(sprintf('<info>Refreshing</info> <comment>%s</comment>', $index));
        $this->indexManager->getIndex($index)->refresh();
    }

    /**
     * Deletes/remaps an index type, populates it, and refreshes the index.
     *
     * @param OutputInterface $output
     * @param string          $index
     * @param string          $type
     * @param boolean         $reset
     */
    private function populateIndexType(OutputInterface $output, $start, $index, $type, $reset)
    {
        if ($reset) {
            $output->writeln(sprintf('Resetting: %s/%s', $index, $type));
            $this->resetter->resetIndexType($index, $type);
        }

        $loggerClosure = function($message) use ($output, $index, $type) {
            $output->writeln(sprintf('Populating: %s/%s, %s', $index, $type, $message));
        };

        if($provider = $this->getSearchProvider($index, $type)) {

            $output->writeln('Lockers file: '.$provider->getLockersFilePath());

            $lockerId = $this->getLockerId($index, $type);

            if($reset) {
                $provider->removeLocker($lockerId);
            }

            $provider->populate($start, $loggerClosure, $lockerId);
        }

        $output->writeln(sprintf('<info>Refreshing</info> <comment>%s</comment>', $index));
        $this->indexManager->getIndex($index)->refresh();
    }
}