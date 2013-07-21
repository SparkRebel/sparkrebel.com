<?php

namespace PW\CelebBundle\Command;

use PW\ApplicationBundle\Command\AbstractCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PW\BoardBundle\Document\Board;
use PW\AssetBundle\Document\Asset;
use PW\CelebBundle\GettyImage;
use PW\CelebBundle\GettyCursor;
use Symfony\Component\Console\Input\InputOption;


class ProcessCommand extends AbstractCommand
{
    private $max = 300;
    protected $redis;

    private $alias = array(
        'Fergie' => "Stacy Ferguson",
        "Khloe Kardashian Odom" => "Khloe Kardashian",
    );

    protected function configure()
    {
        $this
            ->setName('celeb:process')
            ->setDescription('Search and get images for a celeb')
            ->setDefinition(array(
                new InputOption('reset', null, InputOption::VALUE_NONE, 'Rebuild celeb'),
                new InputArgument('search', InputArgument::REQUIRED, 'Search string')
            ));
    }

    protected function getTags(Array $tags, $user)
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
        $search = $input->getArgument('search');
        $reset  = $input->getOption('reset');
        $this->redis = $this->getContainer()->get('snc_redis.default');


        $user = $this->getUserManager()->getRepository()->findOneByName('Celebs');
        if (!$user) {
            $this->getApplication()->find('celeb:create:user')->run(
                new ArrayInput(array('celeb:create:user')), $output
            );
        }

        // @todo Replace with findOrCreateBoard()
        $board = $this->getBoardManager()->getRepository()->findOneBy(array(
            'name' => $search,
            'createdBy.$id' => new \MongoId($user->getId()),
        ));
        if (!$board) {
            throw new \RuntimeException("Board {$search} doesn't exists");
        } else if ($board->getCreatedBy()->getId() != $user->getId()) {
            throw new \RuntimeException("Board {$search} exists but do not belong to celebs");
        }

        $assets    = $this->getDocumentManager()->getRepository('PWAssetBundle:Asset');
        $postm     = $this->getPostManager();
        $assetm    = $this->getAssetManager();

        if (!empty($this->alias[$search])) {
            $search = $this->alias[$search];
        }

        $output->writeln("<info>Search and fetch:</info> <question>{$search}</question>");
        $cursor = $this->getContainer()
            ->get('pw_getty.cursor')->setDefaultData()->peopleSearch()->search($search);

        $postRepo = $this->getDocumentManager()->getRepository('PWPostBundle:Post');
        $postCount = $postRepo->findByBoard($board)
            ->count()->getQuery()->execute();

        $counter = 0;
        $baseTime = time();
        $time = 0;
        $previous_createdGetty_timestamp = time()+19999;
        $same_timestamp_count = 0;
        foreach ($cursor as $search => $image) {
            $created = new \Datetime;
            $created->setTimestamp($baseTime - ($time++));
            $params = array(
                'source' => $image->getId(),
                'description' => $image->getDescription(),
                'createdBy' => $user,
                'created' => $created,
                'meta' => $image->getMeta(),
            );

            $asset = $assets->findOneBySource($image->getId());
            $post  = null;
            if ($asset) {
                $post = $postRepo->findOneBy(array('isActive' => true, 'target.$id' => new \MongoId($asset->getId()), 'board.$id' => new \MongoId($board->getId()), 'isCeleb' => true));

                if ($post && !$reset) {
                    $output->writeln("<info>Asset source already found. Skipping\n</info>");
                    return 0;
                }
            }

            try {
                if (!$asset) {
                    $url = $image->getDownloadUrl();
                    $asset = $assetm->addImageFromUrl($url, $url, $params);
                }

                $createdGetty = $image->getCreated();
                $createdGetty_timestamp = $createdGetty->getTimestamp();
                
                if (time() - $createdGetty_timestamp < 96*60*60) {
                    // getty created less than 96h ago -> use current time for post.created
                    $createdGetty_timestamp = $created->getTimestamp();
                    $createdGetty->setTimestamp($createdGetty_timestamp);
                    $same_timestamp_count = 0;           
                } else {
                    // use getty.created time for post.created
                    if (intval($previous_createdGetty_timestamp) == intval($createdGetty_timestamp)) {
                        $same_timestamp_count++;
                        $createdGetty->setTimestamp($createdGetty_timestamp - $same_timestamp_count);
                    } else {
                        $same_timestamp_count = 0;
                    }
                }
                $previous_createdGetty_timestamp = $createdGetty_timestamp;
                $output->writeln("<info>Setting post.created as ".date('Y-m-d H:i:s',$createdGetty_timestamp)."</info>");
                
                if ($post) {
                    // FIXME: I should update $post with the new data
                    $output->writeln("<info>Updating http://sparkrebel.com/spark/{$post->getId()}</info>");
                    $post->setDescription($params['description']);
                    $post->setTags($image->getTags());
                    $post->setIsCeleb(true);
                    $post->setBoard($board);
                    $post->setCreated($createdGetty);
                    $post->setIsActive(true);
                    $this->getPostManager()->save($post, array('flush' => false, 'validate' => false));
                    $counter++;
                    if ($counter % 10 === 0) {
                        $this->flush();
                    }
                    continue;
                }

                $output->writeln("<info>Downloading {$image->getId()}</info>");
                $post  = $postm->createAssetPost($asset, $params['description'], $user);
                $post->setTags($image->getTags());
                $post->setCreated($createdGetty);
                $post->setIsCeleb(true);
                $post->setPostType('celeb');
                $post->setBoard($board);
                $post->setIsActive(true);
                $board->incPostCount();
                if (count($board->getImages()) < 4) {
                    $board->addImages($asset);
                }

                $this->getBoardManager()->save($board, array('flush' => false, 'validate' => false));
                $this->getPostManager()->save($post, array('flush' => false, 'validate' => false));


            } catch (\Exception $e) {
                $output->writeln("<info>Failed</info> <error>$e</error>");
                continue;
            }

            $postCount++;
            $counter++;

            if ($counter % 10 === 0) {
                $this->flush();
            }     

            if($counter > $this->max && $postCount >= $this->max) {
                $this->flush();
                return 0;
                //exit("Already process {$this->max} items");
            }

        }
    }

    
}
