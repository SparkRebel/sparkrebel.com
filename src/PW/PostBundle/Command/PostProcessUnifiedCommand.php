<?php

namespace PW\PostBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;


class PostProcessUnifiedCommand extends ContainerAwareCommand
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
     * configure
     */
    protected function configure()
    {
        $this
            ->setName('post:process:unified')
            ->setDescription('Processing unified options after post was added')
            ->setDefinition(array(
                new InputArgument(
                    'id',
                    InputArgument::REQUIRED,
                    'The post $id'
                )
            ));
    }

    /**
     *
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $id = $input->getArgument('id');
        $this->dm = $this->getContainer()->get('doctrine_mongodb.odm.document_manager');
        $this->postRepo = $this->dm->getRepository('PWPostBundle:Post');

        $post = $this->postRepo->find($id);
        if (!$post) {
            $output->writeln("<error>Post {$id} doesn't exist</error>");
            return;
        }   
        $total = $this->getContainer()->get('pw_post.post_manager')->updateStream($post);
        if ($total > 0) {
            $output->writeln("<info>Post has been successfully Processed. Added to $total users.</info>");            
        } else {
            $output->writeln("<info>Post is not celeb|brand post</info>");            
        }
        
    }

}
