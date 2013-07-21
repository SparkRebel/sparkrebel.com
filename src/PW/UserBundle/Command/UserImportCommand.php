<?php

namespace PW\UserBundle\Command;

use PW\PostBundle\Document\Post,
    Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;

/**
 * Import a user generated from the export command on a different environment
 */
class UserImportCommand extends ContainerAwareCommand
{

    protected $references = array();

    /**
     * configure
     */
    protected function configure()
    {
        $this
            ->setName('user:import')
            ->setDescription('Import a user\'s data network: user, boards and posts')
            ->setDefinition(array(
                new InputArgument(
                    'userId',
                    InputArgument::REQUIRED,
                    'The user id , email, username or name'
                )
            ));
    }

    /**
     * execute
     *
     * Directly insert data into the db to avoid doctrine munging everything.
     * With the exception of posts, which need to be inserted via doctrine to trigger events
     * and be assigned a sequencial id
     *
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        $this->dm = $this->getContainer()->get('doctrine_mongodb.odm.document_manager');
        $this->repos['assets'] = $this->dm->getRepository('PWAssetBundle:Asset');
        $this->repos['boards'] = $this->dm->getRepository('PWBoardBundle:Board');
        $this->repos['categories'] = $this->dm->getRepository('PWCategoryBundle:Category');
        $this->repos['posts'] = $this->dm->getRepository('PWPostBundle:Post');
        $this->repos['users'] = $this->dm->getRepository('PWUserBundle:User');

        $host = $this->getContainer()->getParameter('mongodb.default.host');
        $port = $this->getContainer()->getParameter('mongodb.default.port');
        $db = $this->getContainer()->getParameter('mongodb.default.name');

        $m = new \Mongo("mongodb://$host:$port");
        $this->db = $m->selectDB($db);

        $userId = $input->getArgument('userId');

        if (file_exists($userId)) {
            $inputFile = $userId;
        } else {
            $userId = preg_replace("@\W+@", '', $userId);
            $inputFile = 'dump/' . $userId . '.json';
            if (!file_exists($inputFile)) {
                $output->writeln("<error>{$inputFile} doesn't exist</error>");
                return;
            }
        }

        $inputJson = file_get_contents($inputFile);
        if (!$inputJson) {
            $output->writeln("<error>{$inputFile} appears to be empty</error>");
            return;
        }

        $data = json_decode($inputJson, true);
        if (!$data) {
            $output->writeln("<error>{$inputFile} does not contain valid json</error>");
            return;
        }

        $this->convertObjectIds($data);
        $this->convertDates($data);

        $this->processReferences($data);
        $this->processUser($data);
        $this->processBoards($data);
        $this->processPosts($data);

        $output->writeln("\ndone");
    }

    /**
     * processReferences
     *
     * Run over the data array and change id values to point to equivalent data.
     *
     * @param array &$data the full export
     */
    protected function processReferences(&$data)
    {
        $this->references = $data['references'];

        $key = $data['user']['_id'];
        if (!is_string($key)) {
            $key = $key->__toString();
        }
        $this->references['users'][$key]['_id'] = $data['user']['_id'];
        unset($data['references']);
        $this->deReference($data);
    }

    /**
     * processUser
     *
     * Create or update the user record to match the data in the export file
     *
     * @param array &$data the full export
     */
    protected function processUser(&$data)
    {
        $userId = $this->references['users'][$data['user']['_id']->__toString()]['_id'];
        if ($userId) {
            $data['user']['_id'] = $userId;
        } else {
            $userId = $data['user']['_id'];
        }
        $id = $userId->__toString();

        $user = $this->db->users->findOne(array('_id' => $userId));
        if ($user) {
            $this->output->writeln("Updating user record for {$data['user']['username']} (id: $id)");
            unset($data['user']['_id']);
            $this->db->users->update(
                array('_id' => $userId),
                array('$set' => $data['user']),
                array('safe' => true)
            );
        } else {
            $this->output->writeln("Creating user record for {$data['user']['username']} (id: $id)");
            $this->db->users->insert($data['user']);
            $user = $this->db->users->findOne(array('_id' => $userId));
        }
        $data['user']['_id'] = $userId;
    }

    /**
     * processBoards
     *
     * Find or create each board in the export data
     *
     * @param array $data the full export
     */
    protected function processBoards($data)
    {
        foreach ($data['boards'] as $board) {
            $exists = $this->db->boards->findOne(array('_id' => $board['_id']));
            if ($exists) {
                $id = $exists['_id']->__toString();
                $this->output->writeln("Found board {$board['name']} (id: $id)");
            } else {
                $id = $board['_id']->__toString();
                $this->output->writeln("Creating board {$board['name']} (id: $id)");
                $board['postCount'] = 0;
                $this->db->boards->insert($board);
            }
        }

        $this->db->boards->update(
            array('createdBy.$id' => $data['user']['_id']),
            array('$set' => array(
                'createdBy.type' => $data['user']['type']
            )),
            array('multiple' => true, 'safe' => true)
        );
    }

    /**
     * processPosts
     *
     * Find or create each post in the export data
     *
     * @param array $data the full export
     */
    protected function processPosts($data)
    {
        foreach ($data['posts'] as $post) {

            if (!empty($post['parent'])) {
                $this->output->writeln("<error>Ignoring import for repost {$post['description']} (id: {$post['_id']})");
                continue;
            }

            $this->convertReferencesToObjects($post);
            $exists = $this->repos['posts']->createQueryBuilder()
                ->field('board')->references($post['board'])
                ->field('createdBy')->references($post['createdBy'])
                ->field('target')->references($post['target'])
                ->getQuery()->execute()->getSingleResult();
            if ($exists) {
                $id = $exists->getId();
                $board = $exists->getBoard()->getName();
                $this->output->writeln("Found post {$post['description']} on board $board (id: $id)");
            } else {
                $board = $post['board']->getName();
                $this->output->writeln("Creating post {$post['description']} on board $board");
                unset($post['_id']);

                try {
                    $new = new Post($post);
                    $this->dm->persist($new);
                    $this->dm->flush();
                } catch (Exception $e) {
                    $this->output->writeln("<error>" . $e->getMessage() . "</error>");
                }
            }
        }

        $this->db->posts->update(
            array('createdBy.$id' => $data['user']['_id']),
            array('$set' => array(
                    'userType' => $data['user']['type'],
                    'createdBy.type' => $data['user']['type']
            )),
            array('multiple' => true, 'safe' => true)
        );
    }

    /**
     * convert string ids to object ids
     *
     * @param mixed &$input an array, probably
     */
    protected function convertObjectIds(&$input)
    {
        if (!is_array($input)) {
            return;
        }

        if (!empty($input['$id']) && is_string($input['$id'])) {
            if (strlen($input['$id']) === 24) {
                if (array_keys($input) === array('$id')) {
                    $input = new \MongoId($input['$id']);
                    return;
                }
                $input['$id'] = new \MongoId($input['$id']);
            }
            return;
        }

        foreach ($input as &$val) {
            $this->convertObjectIds($val);
        }
    }

    /**
     * convert Dates back to objects
     *
     * @param mixed &$input post array initially
     */
    protected function convertDates(&$input)
    {
        if (!is_array($input)) {
            return;
        }

        if (array_keys($input) === array('sec', 'usec')) {
            $input = new \MongoDate($input['sec']);
            return;
        }

        foreach ($input as &$val) {
            $this->convertDates($val);
        }
    }

    /**
     * convertReferencesToObjects
     *
     * To be able to pass the input array to a doctrine model constructor, convert any references
     * to doctrine objects. This is called as part of the post-creation step.
     *
     * @param mixed &$input post array initially
     */
    protected function convertReferencesToObjects(&$input)
    {
        if (!is_array($input)) {
            return;
        }

        if (empty($input['$id'])) {
            foreach ($input as &$val) {
                $this->convertReferencesToObjects($val);
            }
            return;
        }

        $input = $this->repos[$input['$ref']]->findOneBy(array('_id' => ($input['$id'])));
    }

    /**
     * deReference
     *
     * Take an array and for any record which is a reference to another collection look for and
     * replace the array data with the id of the equivalent object in the target database. This
     * accounts for two categories in different installs, with different ids but the same name;
     * and will replace the id in the input array with the id which exists or has just been created.
     *
     * @param mixed &$input probably an array
     */
    protected function deReference(&$input)
    {
        if (!is_array($input)) {
            return;
        }

        if (!empty($input['$id']) && !empty($input['$ref'])) {
            $id = $this->findReference($input['$ref'], $input['$id']);
            if ($input['$id'] != $id) {
                //$this->output->writeln("Swapping {$input['$ref']} reference from {$input['$id']} to $id");
                $input['$id'] = $id;
            }
            return;
        }

        foreach ($input as &$val) {
            $this->deReference($val);
        }
    }

    /**
     * findReference
     *
     * The export data contains a reference and id. If the id is a mongoid and it exists we can be
     * sure that the record is the same entity as the entity being referenced in the export file.
     * If a record with the same id does not exist, then we need to look for an equilvalent record
     * (to avoid creating duplicates) and if it doesn't exist - only then create it.
     *
     * @param string $collection name
     * @param mixed  $id         mongo id or string
     *
     * @return the id to use
     */
    protected function findReference($collection, $id)
    {
        $key = $id;
        if (!is_string($key)) {
            $key = $key->__toString();
        }
        if (empty($this->references[$collection][$key])) {
            return $id;
        }

        $reference = $this->references[$collection][$key];

        if ($reference['_id'] != $id) {
            return $reference['_id'];
        }

        if (is_object($id)) {
            //$this->output->write("\nlooking for $collection $key");
            $exists = $this->db->$collection->findOne(array('_id' => $id));
            if ($exists) {
                //$this->output->write(" found");
            } else {
                if (!empty($reference['hash'])) {
                    $exists = $this->db->$collection->findOne(array('hash' => $reference['hash']));
                } elseif (!empty($reference['username'])) {
                    $exists = $this->db->$collection->findOne(array('username' => $reference['username']));
                } else {
                    if (!empty($reference['name'])) {
                        if (!empty($reference['type'])) {
                            $exists = $this->db->$collection->findOne(array('name' => $reference['name'], 'type' => $reference['type']));
                        } else {
                            $exists = $this->db->$collection->findOne(array('name' => $reference['name']));
                        }
                    }
                }
                if ($exists) {
                    $id = $exists['_id'];
                    //$this->output->write(" found " . $id);
                    $this->references[$collection][$key]['_id'] = $id;;
                } else {
                    //$this->output->writeln(" creating it");
                    $this->db->$collection->insert($reference);
                }
            }
        } else {
            $this->output->writeln("<error> Cannot check for $collection $key automatically </error>");
            //d("shouldn't get here'");
            //die;
        }

        return $id;
    }
}
