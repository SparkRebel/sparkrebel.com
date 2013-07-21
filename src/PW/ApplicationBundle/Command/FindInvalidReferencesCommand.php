<?php

namespace PW\ApplicationBundle\Command;

use Doctrine\Bundle\MongoDBBundle\Command\DoctrineODMCommand;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FindInvalidReferencesCommand extends DoctrineODMCommand
{
    /**
     * @var \MongoCollection
     */
    private $collection;

    /**
     * @var array
     */
    private $postsCache = array();

    /**
     * @var \Closure
     */
    private $printStatusCallback;

    /**
     * @var Doctrine\Common\Persistence\ManagerRegistry
     */
    private $registry;

    private $reference;

    /**
     * @see Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this
            ->setName('doctrine:mongodb:find-invalid-references')
            ->setDescription('Finds invalid references in documents')
            ->setDefinition(array(
                new InputArgument('document', InputArgument::REQUIRED, 'Document name'),
                new InputArgument('reference', InputArgument::REQUIRED, 'Reference name'),
                new InputArgument('referenced_document', InputArgument::REQUIRED, 'Reference model'),
            ));

    }

    /**
     * @see Symfony\Bundle\FrameworkBundle\Command\Command::initialize()
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
         $this->registry = $this->getContainer()->get('doctrine_mongodb');
         $this->printStatusCallback = function() {};
         register_tick_function(array($this, 'printStatus'));
    }

    /**
     * @see Symfony\Component\Console\Command\Command::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $document   = $input->getArgument('document');
        $reference      = $input->getArgument('reference');
        $referenced_document      = $input->getArgument('referenced_document');
        $this->collection = $this->getMongoCollectionForClass($referenced_document);
        $this->reference = $reference;

        $this->migrate($this->getMongoCollectionForClass($document), $output, $reference);
    }

    /**
     * Migrate Foo references in a collection
     *
     * @param \MongoCollection $collection
     * @param OutputInterface  $output
     */
    private function migrate(\MongoCollection $collection, OutputInterface $output, $reference)
    {
        $cursor = $collection->find(array(), array($reference.'.$id' => 1));
        $numProcessed = 0;

        if (!$numTotal = $cursor->count()) {
            $output->writeln(sprintf('There are no "%s" documents to examine.', $collection->getName()));
            return;
        }

        $this->printStatusCallback = function() use ($output, &$numProcessed, $numTotal) {
            $output->write(sprintf("Processed: <info>%d</info> / Complete: <info>%d%%</info>\r", $numProcessed, round(100 * ($numProcessed / $numTotal))));
        };

        declare(ticks=2500) {
            foreach ($cursor as $document) {

                if (!isset($document[$reference]['$id'])) {
                    $output->writeln(sprintf('<error>"%s" document "%s" is missing a '.$reference.' reference</error>', $collection->getName(), $document['_id']));
                }
                else if (!$this->isReferenceValid($document[$reference]['$id'])) {
                    $this->updateDocument($document['_id']);
                    $output->writeln(sprintf('<error>"%s" document "%s" references a nonexistent '.$reference.' "%s"</error>', $collection->getName(), $document['_id'], $document[$reference]['$id']));
                }

                ++$numProcessed;
            }
        }

        $output->write(str_repeat(' ', 28 + ($numProcessed > 0 ? ceil(log10($numProcessed)) : 0)) . "\r");
        $output->writeln(sprintf('Examined <info>%d</info> "%s" documents.', $numProcessed, $collection->getName()));
    }

    /**
     * Determines whether a Original exists with this ID
     *
     * @param \MongoId $post_id
     * @return boolean
     */
    private function isReferenceValid($id)
    {
        return(Boolean) $this->collection->count(array('_id' => $id));
    }

    /**
     * Get the MongoCollection for the given class
     *
     * @param string $class
     * @return \MongoCollection
     * @throws \RuntimeException if the class has no DocumentManager
     */
    private function getMongoCollectionForClass($class)
    {
        if (!$dm = $this->registry->getManagerForClass($class)) {
            throw new \RuntimeException(sprintf('There is no DocumentManager for class "%s"', $class));
        }

        return $dm->getDocumentCollection($class)->getMongoCollection();
    }

    /**
     * Removes corrupted references
     *
     * @param string $id
     */
    private function updateDocument($id)
    {
        $doc = $this->collection->findOne(array('_id' => $id));
        unset($doc[$this->reference]);
        $this->collection->update(array('_id' => $id), $doc);
    }

    /**
     * Invokes the print status callback
     *
     * Since unregister_tick_function() does not support anonymous functions, it
     * is easier to register one method (this) and invoke a dynamic callback.
     */
    public function printStatus()
    {
        call_user_func($this->printStatusCallback);
    }


     /**
     * @see Command
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getArgument('document')) {
            $document = $this->getHelper('dialog')->askAndValidate(
                $output,
                'Please type document name (eg Acme\UserBundle\Document\User):',
                function($document) {
                    if (empty($document)) {
                        throw new \Exception('Document name can not be empty');
                    }

                    return $document;
                }
            );
            $input->setArgument('document', $document);
        }

        if (!$input->getArgument('reference')) {
            $reference = $this->getHelper('dialog')->askAndValidate(
                $output,
                'Please type reference name:',
                function($reference) {
                    if (empty($reference)) {
                        throw new \Exception('Reference can not be empty');
                    }

                    return $reference;
                }
            );
            $input->setArgument('reference', $reference);
        }

        if (!$input->getArgument('referenced_document')) {
            $referenced_document = $this->getHelper('dialog')->askAndValidate(
                $output,
                'Please type reference class: (eg Acme\UserBundle\Document\User)',
                function($referenced_document) {
                    if (empty($referenced_document)) {
                        throw new \Exception('Reference can not be empty');
                    }

                    return $referenced_document;
                }
            );
            $input->setArgument('referenced_document', $referenced_document);
        }

    }
}
