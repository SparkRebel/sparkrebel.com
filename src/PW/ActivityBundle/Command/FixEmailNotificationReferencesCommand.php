<?php

namespace PW\ActivityBundle\Command;

use Doctrine\Bundle\MongoDBBundle\Command\DoctrineODMCommand;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FixEmailNotificationReferencesCommand extends DoctrineODMCommand
{

     /**
     * @var \MongoCollection
     */
    private $notificationsCollection;

     /**
     * @var \MongoCollection
     */

    private $emailsCollection;
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
            ->setName('email:fix-invalid-references')
            ->setDescription('Finds invalid references in Email documents');

    }

    /**
     * @see Symfony\Bundle\FrameworkBundle\Command\Command::initialize()
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->registry = $this->getContainer()->get('doctrine_mongodb');

        $this->notificationsCollection = $this->getMongoCollectionForClass('PW\ActivityBundle\Document\Notification');
        $this->emailsCollection = $this->getMongoCollectionForClass('PW\ActivityBundle\Document\NotificationEmail');

        $this->printStatusCallback = function() {};
        register_tick_function(array($this, 'printStatus'));
    }

    /**
     * @see Symfony\Component\Console\Command\Command::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->migrate($this->getMongoCollectionForClass('PW\ActivityBundle\Document\NotificationEmail'), $output);
    }

    /**
     * Migrate Foo references in a collection
     *
     * @param \MongoCollection $collection
     * @param OutputInterface  $output
     */
    private function migrate(\MongoCollection $collection, OutputInterface $output)
    {
        $cursor = $collection->find(array(), array());
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
                if (count($document['notifications']) > 0 ) {

                    foreach ($document['notifications'] as $notificationId) {

                        if (!$this->isTargetValid($notificationId)) {
                            $output->writeln(sprintf('<error>Email "%s" references a nonexistent Notification "%s"</error>', $document['_id'], $notificationId));
                            $this->updateDocument($document['_id'], $notificationId);
                        }
                    }

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

        return(Boolean) $this->notificationsCollection->count(array('_id' => $id));

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
    private function updateDocument($id, $notificationId)
    {
        $doc = $this->emailsCollection->findOne(array('_id' => $id));
        if(count($doc['notifications']) > 0) {
            foreach ($doc['notifications'] as $key => $notif) {
                if((String)$notif === (String)$notificationId) {
                    unset($doc['notifications'][$key]);
                }
            }    
        }
        
        if (count($doc['notifications']) > 0) {
            $this->emailsCollection->update(array('_id' => $id), $doc);    
        }
        $this->emailsCollection->remove(array('_id' => $id));
        
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
