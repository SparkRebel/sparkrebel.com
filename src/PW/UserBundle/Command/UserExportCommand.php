<?php

namespace PW\UserBundle\Command;

use PW\UserBundle\Document\User,
    Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;

/**
 * Generate a json dump file for a user's data
 *
 * The user board and post data is dumped directly, there's an aditional "references"
 * key included which stores anything at all that the user, board or post data points at.
 * This means mainly assets, but also of interest categories.
 *
 * User preferences are not manipulated, which probably means they are invalid once imported
 * (because they are not stored in the db as mongoids, the reference-searching logic doesn't
 * find them and no special case has been added for user preferences)
 *
 * A warning is shown when generating an export file if there are any references to assets
 * which are local to the current install. If these assets are not reprocessed and the dump file
 * regenerated, when imported there will be some missing images. It's deliberately not a
 * roadblock to prevent an unprocessable asset from blocking the export process.
 */
class UserExportCommand extends ContainerAwareCommand
{

    protected $references = array();

    /**
     * configure
     */
    protected function configure()
    {
        $this
            ->setName('user:export')
            ->setDescription('Export a user\'s data network: user, boards and posts')
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
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $host = $this->getContainer()->getParameter('mongodb.default.host');
        $port = $this->getContainer()->getParameter('mongodb.default.port');
        $db = $this->getContainer()->getParameter('mongodb.default.name');

        $m = new \Mongo("mongodb://$host:$port");
        $db = $m->selectDB($db);

        $userCollection = $db->selectCollection('users');
        $boardCollection = $db->selectCollection('boards');
        $postCollection = $db->selectCollection('posts');

        $userId = $input->getArgument('userId');

        $user = $userCollection->findOne(array('_id' => new \MongoId($userId)));
        if (!$user) {
            $user = $userCollection->findOne(
                array(
                    '$or' => array(
                        array('email'    => $userId),
                        array('username' => $userId),
                        array('name'     => $userId),
                    )
                )
            );
            if (!$user) {
                $output->writeln("<error>User {$userId} could not be found</error>");
                return;
            }
        }
        $output->write("Processing user {$user['username']} (id: {$user['_id']})\n");

        $userId = $user['_id'];

        $boards = iterator_to_array($boardCollection->find(
            array(
                'createdBy.$id' => $userId,
                'isSystem' => false
            )
        ));
        $count = count($boards);
        $output->write("\tfound $count boards\n");
        foreach ($boards as $board) {
            $output->write("\t\t{$board['name']} (id: {$board['_id']})\n");
        }

        $posts = iterator_to_array($postCollection->find(
            array(
                'createdBy.$id' => $userId,
                'target.$ref' => 'assets'
            )
        ));
        $count = count($posts);
        $output->write("\tfound $count posts\n");
        foreach ($posts as $post) {
            $output->write("\t\t{$post['description']} (id: {$post['_id']})\n");
        }
        $this->out = compact('user', 'boards', 'posts');

        $this->stripDollarDb($this->out);
        $this->collectReferences($this->out);

        foreach ($this->references as $collection => $ids) {
            if ($collection === 'users') {
                foreach (array_keys($ids) as $id) {
                    if ($id === $userId->__toString()) {
                        unset($ids[$id]);
                    }
                }
                if (!$ids) {
                    unset($this->references['users']);
                }
                continue;
            }
            foreach (array_keys($ids) as $id) {
                if (!empty($this->out[$collection][$id])) {
                    unset($this->references[$collection][$id]);
                }
            }
        }

        $this->references = array_filter($this->references);
        foreach ($this->references as $collection => &$ids) {
            $collection = $db->selectCollection($collection);
            $ids = iterator_to_array($collection->find(array('_id' => array('$in' => array_values($ids)))));
        }
        $this->references = array_filter($this->references);
        if ($this->references) {
            $this->out['references'] = $this->references;
            $output->write("\tAdditional references included in export\n");
            foreach ($this->out['references'] as $collection => $references) {
                $count = count($references);
                $output->write("\t\t$count $collection\n");
                foreach ($references as $reference) {
                    $name = '???';
                    if (!empty($reference['url'])) {
                        $name = $reference['url'];
                    } elseif (!empty($reference['name'])) {
                        $name = $reference['name'];
                    } elseif (!empty($reference['description'])) {
                        $name = $reference['description'];
                    }
                    $output->write("\t\t\t{$name} (id: {$reference['_id']})\n");
                }
            }
        }

        if (!empty($this->out['references']['assets'])) {
            $missingAssets = array();
            foreach ($this->out['references']['assets'] as $asset) {
                if ($asset['url'][0] === '/') {
                    $missingAssets[] = $asset['hash'];
                }
            }

            if ($missingAssets) {
                $count = count($missingAssets);
                $output->writeln("\n\t<error> $count MISSING ASSETS FOUND </error>\n");
                $output->writeln("Run the following commands to correct (fastest if ran on this install):\n");
                foreach ($missingAssets as $hash) {
                    $output->writeln("app/console --env=prod --no-debug asset:sync $hash");
                }

                $output->writeln("\nThe dump file must be regenerated to reflect changes to assets\n");
            }
        }

        $string = json_encode($this->out);

        $name = preg_replace("@\W+@", '', $user['username']);
        $filename = 'dump/' . $name . '.json';
        $output->write("creating $filename\n");
        file_put_contents($filename, $string);
    }

    /**
     * stripDollarDb
     *
     * Make sure we don't store $db keys - if we do we'll end up with doctrine trying to read from a
     * db which likely doesn't exist on the imported server
     *
     * @param mixed &$input an array, probably
     */
    protected function stripDollarDb(&$input)
    {
        if (!is_array($input)) {
            return;
        }

        if (!empty($input['$db'])) {
            unset($input['$db']);
            return;
        }

        foreach ($input as &$val) {
            $this->stripDollarDb($val);
        }
    }

    /**
     * collectReferences
     *
     * Recurse on the input array, and make a record of each db-ref that is found
     * This is stored in addition to the user's data dump to allow correcting ids where data is
     * Different in the db the data is imported to.
     *
     * @param mixed &$input an array, probably
     */
    protected function collectReferences(&$input)
    {
        if (!is_array($input)) {
            return;
        }

        if (!empty($input['$id']) && !empty($input['$ref'])) {
            $key = $input['$id'];
            if (!is_string($key)) {
                $key = $key->__toString();
            }
            if (empty($this->out[$input['$ref']][$key])) {
                $this->references[$input['$ref']][$key] = $input['$id'];
            }
            return;
        }

        foreach ($input as &$val) {
            $this->collectReferences($val);
        }
    }
}
