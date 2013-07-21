<?php

namespace PW\PostBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;


class PostAssetFixCommand extends ContainerAwareCommand
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
            ->setName('post:asset:fix')
            ->setDescription('Fixes post asset from original source')
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

        $output->writeln("<info>Processing Post {$post->getId()}</info>");
        $asset = $post->getImage();
        $board = $post->getBoard();
        if(!$post->getIsVideoPost()) {
            $new_img = $this->getContainer()->get('pw.asset')->addImageFromUrl($asset->getSourceUrl(), $asset->getSourcePage(), null, true);
        } else {
            $sparker = new \PW\PostBundle\Model\VideoSparker($post->getImage()->getSourceUrl());
            $new_img = $this->getContainer()->get('pw.asset')->addImageFromUrl($sparker->getBigThumbnailUrl(), $asset->getSourcePage(), null, true);
        }
        //failed to add image form url
        if($new_img === false) {
        	/*$board->removeImage($post->getImage());
        	$this->dm->persist($board);
        	$this->dm->flush();*/
        	//$this->getContainer()->get('pw_post.post_manager')->delete($post);
        	//$output->writeln("<error>Original asset does not exist anymore. Deleting post.</error>");
        } else {
        	$this->dm->persist($new_img);
	        $board->removeImage($post->getImage());
	        $board->addImages($new_img);
	        $this->dm->persist($board);
	        $post->setImage($new_img);
	        $this->dm->persist($post);
	        $this->dm->flush();

	        $output->writeln("<info>Post has been successfully Processed</info>");
        }



    }

}
