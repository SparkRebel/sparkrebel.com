<?php

namespace PW\PostBundle\Command;

use PW\UserBundle\Document\FollowPost,
    Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\Console\Input\InputOption;

/**
 * When a new post is created, we need to create references in the follow_posts collection for all
 * followers.
 */
class ProcessCuratorPostsCommand extends ContainerAwareCommand
{
    /**
     * document manager placeholder instance
     */
    protected $dm;

    /**
     * post repo placeholder instance
     */
    protected $postRepo;

    /**
     */
    protected $limit = 5;


    /**
     * configure
     */
    protected function configure()
    {
        $this
            ->setName('post:process:curator')
            ->setDescription('Processess all curator posts')
            ->setDefinition(array(
                new InputOption('postId', null, InputOption::VALUE_NONE, 'Process given post')
            ));
    }

    /**
     * execute
     * Execute post repo
     *
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {


        $t = date('Y-m-d H:i');

        $this->dm = $this->getContainer()->get('doctrine_mongodb.odm.document_manager');
        $this->postRepo = $this->dm->getRepository('PWPostBundle:Post');

        $total = $this->postRepo->countCuratorSparksToByProcessed();

        if($total === 0) {
            $output->writeln("<error>No curator posts to process. {$t}</error>");
            return;
        }

        if($input->getOption('postId')) {
            $post = $this->postRepo->find($input->getOption('postId'));
            if (!$post) {
                $output->writeln("<error>Post with given id could not be found</error>");
                return;
            } elseif (!$post->getIsCuratorPost()) {
                $output->writeln("<error>Post is not a curator post</error>");
                return;
            }
            $posts = array($post);
        } else {

            $total_to_process = 1;
            if($total >= 150 && $total < 300) {
                $total_to_process = 2;
            } elseif ($total >=300) {
                $total_to_process = 3;
            }

            $posts = $this->postRepo->findCuratorSparksToByProcessed()
            ->limit($total_to_process)
            ->skip(mt_rand(0, $total - 1))
            ->getQuery()->execute();
        }

        



        foreach ($posts as $doc) {
        	$this->getContainer()->get('pw.event')->publish(
                'post.create',
                array(
                    'postId' => $doc->getId(),
                    'userId' => $doc->getCreatedBy()->getId(),
                ),
                'high'
            );

            $parent = $doc->getParent();
            if ($parent) {
                $this->getContainer()->get('pw.event')->publish(
                    'post.repost',
                    array(
                        'postId' => $doc->getId(),
                        'userId' => $doc->getCreatedBy()->getId(),
                        'parentPostId' => $parent->getId(),
                        'parentUserId' => $parent->getCreatedBy()->getId(),
                    )
                );
            }
			$doc->setIsCuratorPostAlreadyProcessed(true);
			$doc->setDeleted(null);
			$doc->setIsActive(true);
            $doc->setCreated(new \DateTime);

			$this->getContainer()->get('pw_post.post_manager')->save($doc);

            $board = $doc->getBoard();
            if (count($board->getImages()) < 4) {
                $board->addImages($doc->getImage());  
                $board->incPostCount();                          
                $this->getContainer()->get('pw_board.board_manager')->save($board);

            }
            
            $output->writeln("<info>Processed Post {$doc->getId()}. {$t}</info>");

        }
    }


}
