<?php

namespace PW\ItemBundle\Command;

use PW\BoardBundle\Document\Board,
    PW\ApplicationBundle\Document\Exception\ConstraintViolationException,
    Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\NullOutput;

/**
 * Process one specific feed_items entry
 */
class FeedItemStep3Command extends ContainerAwareCommand
{
    protected $dm;
    protected $repos = array();

    protected function configure()
    {
        $this
            ->setName('feed:item:step3')
            ->setDescription('Publish the post')
            ->setDefinition(array(
                new InputArgument(
                    'id',
                    InputArgument::REQUIRED,
                    'The feed-item fid'
                )
            ));
    }

    protected function setupRepos()
    {
        $this->dm = $this->getContainer()->get('doctrine_mongodb.odm.document_manager');

        $this->repos['Board']    = $this->dm->getRepository('PWBoardBundle:Board');
        $this->repos['FeedItem'] = $this->dm->getRepository('PWItemBundle:FeedItem');
        $this->repos['Item']     = $this->dm->getRepository('PWItemBundle:Item');
        $this->repos['Post']     = $this->dm->getRepository('PWPostBundle:Post');

    }

    /**
     * Find the followers for boards or the author and add a stream entry for the newly created
     * post for each follower
     *
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setupRepos();
        $this->output = $output;
        $id    = $input->getArgument('id');
        $posts = $this->processItem($id, true);

        if (count($posts) == 0) {
            $output->writeln("<comment>No posts were generated for: {$id}</comment>");
        } else {
            foreach ($posts as $post) {
                $output->writeln("<info>Post generated for item {$id}:</info> {$post->getId()} - {$post->getTarget()->getName()}");
            }
            return true;
        }
    }

    /**
     * Accepts a feed item and converts it into all the objects it represents. Including:
     *  Brand
     *  Category
     *  Merchant
     *  User
     *  Item
     *
     * @param mixed $feedItem instance or id
     *
     * @return array of generated/updated items
     */
    protected function processItem($feedItem)
    {
        if (!is_object($feedItem)) {
            $id = $feedItem;
            $feedItem = $this->repos['FeedItem']->findOneBy(array('fid' => $id));
            if (!$feedItem) {
                throw new \Exception("Feed Item $id doesn't exist");
            }
        }
        if (!$this->output) {
            $this->output = new NullOutput();
        }

        $fid = $feedItem->getFid();
        $item = $this->repos['Item']->findOneBy(array('feedId' => $fid));

        if (!$item) {
            throw new \Exception("Item for fid $id doesn't exist");
        }
        if (!$item->getImagePrimary()) {
            throw new \Exception("Item for fid $id doesn't have an image");
        }

        $onSale = $item->getIsOnSale();
        $topCategories = array();
        $categories = $item->getCategories();
        foreach ($categories as $category) {
            $parent = $category->getParent();
            if ($parent) {
                $boardCat = $parent;
            } else {
                $boardCat = $category;
            }
            $name = $boardCat->getName();
            $topCategories[$name] = $boardCat;
        }

        $brandName = $item->getBrandName();
        $brandUser = $item->getBrandUser();
        $merchantName = $item->getMerchantName();
        $merchantUser = $item->getMerchantUser();
        //$this->output->writeln("<comment>Merchant name:'".$merchantName."', Merchant user get_class:".get_class($merchantUser)."</comment>");

        $result = $boards = array();

        //check if there are any previous posts for this item
        $postedBefore = $this->hasBeenPostedBefore($item);

        $postManager = $this->getContainer()->get('pw_post.post_manager');

        if ($merchantUser !== null) {
            foreach ($topCategories as $category) {
                $board = $this->findOrCreateBoard($merchantUser, $category->getName(), $category);

                //if it's been posted before, there is a chance we just need to move it to the right board
                if ($board && $postedBefore) {
                	$board = $this->moveOrReturnBoard($merchantUser, $item, $board);
                }

                if ($board) {
                    $boards[] = array($board, $merchantUser);
                }
            }

            /*
            if ($brandUser && $brandUser !== $merchantUser) {
                $boardName = "$brandName @ $merchantName";
                if (!preg_match("/\.com\z/i", $boardName)) {
                  $boardName .= '.com';
                }
                $board = $this->findOrCreateBoard($merchantUser, $boardName);
                if ($board) {
                    $boards[] = array($board, $merchantUser);
                }
            }
            */

            if ($onSale) {
                $board = $this->findOrCreateBoard($merchantUser, 'Sale');
                if ($board) {
                    $boards[] = array($board, $merchantUser);
                }
            }
        }

        if ($brandUser && $brandUser !== $merchantUser) {
            foreach ($topCategories as $category) {
                $board = $this->findOrCreateBoard($brandUser, $category->getName(), $category);

                //if it's been posted before, there is a chance we just need to move it to the right board
                if ($board && $postedBefore) {
                	$board = $this->moveOrReturnBoard($brandUser, $item, $board);
                }

                if ($board) {
                    $boards[] = array($board, $brandUser);
                }
            }

            /*
            $board = $this->findOrCreateBoard($brandUser, "$brandName @ $merchantName");
            if ($board) {
                $boards[] = array($board, $brandUser);
            }
            */

            if ($onSale) {
                $board = $this->findOrCreateBoard($brandUser, 'Sale');
                if ($board) {
                    $boards[] = array($board, $brandUser);
                }
            }
        }


        foreach ($boards as $board) {
            list($board, $user) = $board;
            $this->output->writeln("<comment>trying save item post to board: ".$board->getName()."</comment>");
            $data = array(
                'board' => $board,
                'description' => $item->getDescription(),
                'link' => $item->getLink(),
                'createdBy' => $user,
                'target' => $item,
                'image' => $item->getImagePrimary()
            );
            $post = $postManager->create($data);
            $post->setPostType('brand');
            try {
                $postManager->save($post, array('validate' => true));
                $result[] = $post;
            } catch (ConstraintViolationException $e) {
                // Update all images from the targets
                $query = array('target.$id' => $item->getId(), 'target.$ref'=> 'items');
                foreach ($this->repos['Post']->find($query) as $post) {
                    $post->setImage($item->getImagePrimary());
                    $this->dm->persist($post);
                }
            }
        }

        if (!$item->getRootPost() && !empty($result)) {
            $item->setRootPost($result[0]);
            $this->dm->persist($item);
        }

        $feedItem->setStatus('processed');
        $this->dm->persist($feedItem);
        $this->dm->flush(null, array('safe' => false, 'fsync' => false));

        return $result;
    }

    /**
     * findOrCreateBoard
     *
     * @param mixed $user     instance
     * @param mixed $name     name of the board to find/crate
     * @param mixed $category instance
     *
     * @return  board instance
     */
    protected function findOrCreateBoard($user, $name, $category = null)
    {
        $board = $this->repos['Board']->findOneBy(
            array(
                'createdBy.$id' => new \MongoId($user->getId()),
                'name' => $name
            )
        );

        if ($board) {
            if ($category && !$board->getCategory()) {
                $board->setCategory($category);
                $this->dm->persist($board);
                $this->dm->flush(null, array('safe' => false, 'fsync' => false));
            }
            return $board;
        }

        $board = new Board();
        $board->setCreatedBy($user);
        $board->setName($name);
        if ($category) {
            $board->setCategory($category);
        }
        $board->setIsSystem(true);
        $this->dm->persist($board);
        $this->dm->flush(null, array('safe' => false, 'fsync' => false));

        return $board;
    }


    /**
     * hasBeenPostedBefore
     *
     * @param mixed $$item    instance
     *
     * @return boolean
     */
	protected function hasBeenPostedBefore($item) {
        $post = $this->repos['Post']->createQueryBuilder()
			->field('target')->references($item)
			->getQuery()->execute()->getSingleResult();

		if ($post) {
			return true;
		}

		return false;
	}

    /**
     * moveOrReturnBoard
     *
     * @param mixed $user     instance
     * @param mixed $item     the relevant item to check
     * @param mixed $board	  the board to move to, if possible
     *
     * @return mixed (Board instance or null)
     */
	protected function moveOrReturnBoard($user, $item, $board) {
		$post = $this->repos['Post']->createQueryBuilder()
			->field('target')->references($item)
			->field('createdBy')->references($user)
			->field('category')->exists(true)
			->getQuery()->execute()->getSingleResult();

		if (!$post) {
			return $board;
		}

    $boardManager = $this->getContainer()->get('pw_board.board_manager');
		$boardManager->changeBoard($post, $board);

		return null;
	}
}
