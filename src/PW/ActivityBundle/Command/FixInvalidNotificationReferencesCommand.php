<?php

namespace PW\ActivityBundle\Command;

use Doctrine\Bundle\MongoDBBundle\Command\DoctrineODMCommand;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FixInvalidNotificationReferencesCommand extends DoctrineODMCommand
{
    /**
     * @var \MongoCollection
     */
    private $followCollection;

     /**
     * @var \MongoCollection
     */
    private $notificationsCollection;
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

    /**
     * @see Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this
            ->setName('notification:find-invalid-references')
            ->setDescription('Finds invalid references in Post documents');

    }

    /**
     * @see Symfony\Bundle\FrameworkBundle\Command\Command::initialize()
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
         $this->registry = $this->getContainer()->get('doctrine_mongodb');

         $this->followCollection = $this->getMongoCollectionForClass('PW\UserBundle\Document\Follow');
         $this->notificationsCollection = $this->getMongoCollectionForClass('PW\ActivityBundle\Document\Notification');
         $this->printStatusCallback = function() {};
         register_tick_function(array($this, 'printStatus'));
    }

    /**
     * @see Symfony\Component\Console\Command\Command::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->migrate($this->getMongoCollectionForClass('PW\ActivityBundle\Document\Activity'), $output);
    }

    /**
     * Migrate Foo references in a collection
     *
     * @param \MongoCollection $collection
     * @param OutputInterface  $output
     */
    private function migrate(\MongoCollection $collection, OutputInterface $output)
    {
        $cursor = $collection->find(array(), array('target.$id' => 1));
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
                if (!isset($document['target']['$id'])) {
                    $output->writeln(sprintf('<error>"%s" document "%s" is missing a Target reference</error>', $collection->getName(), $document['_id']));
                }
                else if (!$this->isTargetValid($document['target']['$id'])) {
                    $this->deleteActivities($document['_id']);
                    $output->writeln(sprintf('<error>"%s" document "%s" references a nonexistent Target "%s"</error>', $collection->getName(), $document['_id'], $document['target']['$id']));
                }

                ++$numProcessed;
            }
        }

        $output->write(str_repeat(' ', 28 + ($numProcessed > 0 ? ceil(log10($numProcessed)) : 0)) . "\r");
        $output->writeln(sprintf('Examined <info>%d</info> "%s" documents.', $numProcessed, $collection->getName()));
    }

    /**
     * Determines whether a Target exists with this ID
     *
     * @param \MongoId $post_id
     * @return boolean
     */
    private function isTargetValid($id)
    {

        return(Boolean) $this->followCollection->count(array('_id' => $id));

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
    private function deleteActivities($id)
    {
        $this->notificationsCollection->remove(array('_id' => $id));
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
}
