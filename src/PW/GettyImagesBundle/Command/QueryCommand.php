<?php

namespace PW\GettyImagesBundle\Command;

use PW\ApplicationBundle\Command\AbstractCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class QueryCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('getty:query')
            ->setDescription('Perform a Getty Images query and download each result/image')
            ->setDefinition(array(
                new InputArgument('keywords', InputArgument::REQUIRED, 'Search Keywords'),
                new InputArgument('board', InputArgument::REQUIRED, 'Board name to Spark to'),
                new InputOption('limit', '-l', InputOption::VALUE_OPTIONAL, 'Limit to X results', 500),
                new InputOption('reset', '-r', InputOption::VALUE_NONE, 'Continue if existing found'),
            ))
        ;
    }

    protected function getTags(array $tags, $user)
    {
        $dm   = $this->getContainer()->get('doctrine_mongodb.odm.document_manager');
        $tagm = $dm->getRepository('PWTagBundle:Tag');
        $ret  = array();
        foreach ($tags as $tag) {
            if (!($dtag = $tagm->findOneById($tag))) {
                $dtag = new \PW\TagBundle\Document\Tag;
                $dtag->setId($tag);
                $dtag->setCreatedBy($user);
                $dm->persist($dtag);
            }
            $ret[] = $dtag;
        }

        return $ret;
    }

    protected function flush()
    {
        $this->getBoardManager()->flush();
        $this->getPostManager()->flush();
    }

    /**
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $keywords  = $input->getArgument('keywords');
        $boardName = $input->getArgument('board');
        $limit     = $input->getOption('limit');
        $reset     = $input->getOption('reset');

        $username = $this->getContainer()->getParameter('pw.system_user.sparkrebel.username');
        $user     = $this->getUserManager()->getRepository()->findOneByUsername($username);
        if (!$user) {
            $output->writeln("<error>System User not found... creating...</error>");
            $this->getApplication()->find('sr:create:user')->run(new ArrayInput(array($this->getContainer()->getParameter('pw.system_user.sparkrebel.name'))), $output);
        }

        $user = $this->getUserManager()->getRepository()->findOneByUsername($username);
        if (!$user) {
            throw new \RuntimeException("There was a problem finding/creating system User: {$username}");
        }

        $board = $this->getBoardManager()->findOrCreateBoard($boardName, $user);
        if (!$board) {
            throw new \RuntimeException("There was a problem finding/creating Board: {$boardName}");
        }

        $assets = $this->getDocumentManager()->getRepository('PWAssetBundle:Asset');
        $output->writeln("<info>Search and fetch:</info> <question>{$keywords}</question>");
        $cursor = $this->getContainer()
            ->get('pw_getty.cursor')
            ->setDefaultData()
            ->search($keywords)
            ->limit($limit);

        $postCount = $this->getPostManager()
            ->getRepository()
            ->findByBoard($board)
            ->count()->getQuery()->execute()
        ;

        $counter  = 0;
        $baseTime = time();
        $time     = 0;
        foreach ($cursor as $keywords => $image) {
            // What is this timestamp nonsense?
            $created = new \Datetime();
            $created->setTimestamp($baseTime - ($time++));
            $params = array(
                'source'      => $image->getId(),
                'description' => $image->getDescription(),
                'createdBy'   => $user,
                'created'     => $created,
                'meta'        => $image->getMeta(),
            );

            try {
                $post = null;
                if ($asset = $assets->findOneBySource($image->getId())) {
                    // Asset exists already, what about Post?
                    $post = $this->getPostManager()
                        ->getRepository()
                        ->createQueryBuilder()
                        ->field('isActive')->equals(true)
                        ->field('target')->references($asset)
                        ->field('board')->references($board)
                        ->getQuery()->getSingleResult()
                    ;
                    if ($post && !$reset) {
                        $output->writeln("<info>Reached an Asset/Post that already exists. Exiting...</info>");
                        $this->flush();
                        return false;
                    }
                } else {
                    // Download image and create our Asset record
                    $url   = $image->getDownloadUrl();
                    $output->writeln('<info>$asset = $this->getAssetManager()->addImageFromUrl(</info> '.$url);
                    $asset = $this->getAssetManager()->addImageFromUrl($url, $url, $params);
                }

                if (!empty($post)) {
                    // Update an existing Post
                    $output->writeln("<info>Updating:</info> http://sparkrebel.com/spark/{$post->getId()} - {$image->getId()}");
                    $post->setDescription($params['description']);
                    $post->setTags($image->getTags());
                    $post->setBoard($board);
                    $post->setIsCeleb(true);
                } else {
                    // Download image and create new Post
                    $output->writeln("<info>Downloading:</info> {$image->getId()}");
                    $post  = $this->getPostManager()->createAssetPost($asset, $params['description'], $user);
                    $post->setTags($image->getTags());
                    $post->setCreated($created);
                    $post->setBoard($board);
                    $post->setIsCeleb(true);
                    $board->incPostCount();
                    if (count($board->getImages()) < 4) {
                        $board->addImages($asset);
                    }
                }

                $this->getBoardManager()->save($board, array('flush' => false, 'validate' => false));
                $this->getPostManager()->save($post, array('flush' => false, 'validate' => false));
            } catch (\Exception $e) {
                $output->writeln("<error>Error! {$e->getMessage()}</error>");
            }

            $counter++;
            if ($counter % 10 === 0) {
                $this->flush();
            }

            if ($postCount >= $limit && $counter > $limit) {
                $this->flush();
                $output->writeln("<info>Processed maximum allowed:</info> {$limit}");
                return false;
            }
        }
    }
}
