<?php

namespace PW\PostBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PW\ApplicationBundle\Resources\ProgressBar;
use PW\UserBundle\Document\FollowPost;

/**
 * A testing/dev only command
 *
 * Populates user streams for all posts. There is a unique index on user-post so duplicates will
 * be discarded by the database - therefore this shell can be re-run
 */
class PostProcessAllCommand extends PostProcessCommand
{
    /**
     * configure
     */
    protected function configure()
    {
        $this
            ->setName('follow:post:processAll')
            ->setDescription('For dev/testing. Ensure follow-post records exist for all boards that are being followed')
            ->setDefinition(array(
                new InputArgument('id', InputArgument::OPTIONAL, 'The board $id')
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
        $limit = 10;
        $id = $input->getArgument('id');

        $this->dm = $this->getContainer()
            ->get('doctrine_mongodb.odm.document_manager');
        $this->postRepo = $this->dm->getRepository('PWPostBundle:Post');
        $followRepo = $this->dm->getRepository('PWUserBundle:Follow');

        $conditions = array(
            'target.$ref' => 'boards',
        );

        $total = $followRepo->findBy($conditions)->count();

        echo ProgressBar::start($total, 'Processing board follows');

        while (true) {
            $follows = $followRepo
                ->findBy($conditions)
                ->sort(array('id' => true))
                ->limit($limit);

            $follow = null;
            foreach ($follows as $follow) {
                $this->processBoard($follow);
                echo ProgressBar::next();
            }

            if (!$follow) {
                break;
            }

            $lastId = $follow->getId();
            $conditions['id']['$gt'] = new \MongoId($lastId);
        }

        echo ProgressBar::finish();

        $this->getEventManager()->requestJob('stream:refresh brands brandOnsale', 'low', '', '', 'feeds');
    }

    /**
     * processBoard
     *
     * @param mixed $follow instance
     */
    protected function processBoard($follow)
    {

        $limit = 10;

        $board = $follow->getTarget();
        $conditions = array(
            'board' => $board->getId()
        );

        while (true) {
            $posts = $this->postRepo
                ->findBy($conditions)
                ->sort(array('id' => true))
                ->limit($limit);

            $post = null;
            foreach ($posts as $post) {
                $this->processFollow($follow, $post);
            }

            if (!$post) {
                break;
            }

            $lastId = $post->getId();
            $conditions['id']['$gt'] = new \MongoId($lastId);

            $this->dm->flush(null, array('safe' => false, 'fsync' => false));
        }
    }
}
