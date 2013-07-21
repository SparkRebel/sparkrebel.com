<?php

namespace PW\BoardBundle\Model;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use PW\ApplicationBundle\Model\AbstractManager;
use PW\BoardBundle\Document\Board;
use PW\PostBundle\Document\Post;
use PW\UserBundle\Document\User;

/**
 * @method \PW\BoardBundle\Repository\BoardRepository getRepository()
 * @method \PW\BoardBundle\Document\Board find() find(string $id)
 * @method \PW\BoardBundle\Document\Board create() create(array $data)
 * @method void delete() delete(\PW\BoardBundle\Document\Board $board, \PW\UserBundle\Document\User $deletedBy, bool $safe, bool $andFlush)
 */
class BoardManager extends AbstractManager
{
    const DEFAULT_WISHLIST = 'Wishlist';
    const DEFAULT_WISHLIST_CATEGORY = 'Gifts & Wish Lists';

    /**
     * @var array
     */
    protected $defaultBoards = array(
        // Name                   // Category
        self::DEFAULT_WISHLIST => self::DEFAULT_WISHLIST_CATEGORY
    );

    /**
     * @param string $name
     * @param \PW\UserBundle\Document\User $createdBy
     * @return \PW\BoardBundle\Document\Board
     */
    public function findOrCreateBoard($name, User $createdBy = null)
    {
        $qb = $this->getRepository()
            ->createQueryBuilder()
            ->field('name')->equals($name)
        ;

        if ($createdBy) {
            $qb->field('createdBy')->references($createdBy);
        }

        $board = $qb->getQuery()->getSingleResult();
        if (!$board) {
            $board = $this->create();
            $board->setName($name);
            $board->setCreatedBy($createdBy);
            $this->save($board, array('validate' => false));
        }

        return $board;
    }

    /**
     * getCategorized
     *
     * @param array $user instance
     *
     * @return array
     */
    public function getCategorized($user)
    {
        $return = $curated = $following = $category = $store = $sale = array();

        $boards = $this->getRepository()
            ->createQueryBuilder()
            ->field('images')->prime()
            ->field('createdBy')->references($user)
            ->field('isActive')->equals(true)
            ->getQuery()->execute();

        foreach ($boards as $board) {
            if ($board->getDeleted() || $board->getPostCount() < 1) {
                continue;
            }

            $i = str_pad($board->getPostCount(), 6, '0', STR_PAD_LEFT) . $board->getName();

            if ($board->getIsSystem()) {
                $catName = $board->getCategory();
                $name = $board->getName();

                if (!$catName && strpos($name, '@')) {
                    $store[$i] = $board;
                    continue;
                }

                if ($name === 'Sale') {
                    $sale[$i] = $board;
                    continue;
                }

                if (!$catName) {
                    continue; // This shouldn't ever happen
                }
                $category[$i] = $board;
            } else {
                $curated[$i] = $board;
            }
        }

        if ($curated) {
            ksort($curated);
            $return['Curated'] = array_values($curated);
        }
        if ($following) {
            ksort($following);
            $return['Following'] = array_values($following);
        }
        if ($category) {
            ksort($category);
            $return['By Category'] = array_values($category);
        }
        if ($store) {
            ksort($store);
            $title = 'By Brand/Store';
            $return[$title] = array_values($store);
        }
        if ($sale) {
            $return['Sale'] = $sale;
        }

        return $return;
    }

    /**
     * processCounts
     *
     * @param Board $board instance
     */
    public function processCounts(Board $board)
    {
        if (!$board || $board->getDeleted()) {
            return;
        }

        $postCount = $this->dm->getRepository('PWPostBundle:Post')
            ->findByBoard($board)
            ->count()->getQuery()->execute();

        $followerCount = $this->dm->getRepository('PWUserBundle:Follow')
            ->findFollowersByBoard($board)
            ->hint(array('target.$ref' => 1, 'target.$id' => 1)) // point mongo to right index
            ->count()->getQuery()->execute();

        $board->setPostCount($postCount);
        $board->setFollowerCount($followerCount);
        $this->save($board, array('validate' => false));
    }

    /**
     * changeBoard
     *
     * @param Post $post instance
     * @param Board $board instance
     */
    public function changeBoard(Post $post, Board $board)
    {
		$oldBoard = $post->getBoard();
		$post->setBoard($board);

		if ($post->getCategory() == $oldBoard->getCategory()) {
			$post->setCategory($board->getCategory());
		}

		$this->dm->persist($post);
		$this->dm->flush();

		$this->processCounts($oldBoard);
		$this->processCounts($board);
	}

    /**
     * @param \PW\BoardBundle\Document\Board $board
     * @return \PW\BoardBundle\Document\Board
     */
    public function getDuplicate(Board $board)
    {
        return $this->getRepository()
            ->findDuplicates($board)
            ->getQuery()->getSingleResult();
    }

    /**
     * @param User $user
     * @param bool $repost
     * @return int
     */
    public function getBoardCountForUser(User $user, $includeDefault = true)
    {
        $qb = $this->getRepository()->findByUser($user);
        if (!$includeDefault) {
            $qb->field('name')->notEqual(self::DEFAULT_WISHLIST);
        }

        return $qb->count()->getQuery()->execute();
    }

    /**
     * @return array
     */
    public function getDefaultBoards()
    {
        return $this->defaultBoards;
    }

    /**
     * changes the owner of board
     *
     * @param Board $board
     * @param User $new_owner
     * @return void
     */
    public function changeOwner(Board $board, User $new_owner)
    {
        $board->setCreatedBy($new_owner);
        $this->dm->persist($board);
		$this->dm->flush();
		$this->userManager->processBoardCounts($new_owner);
    }

    /**
     * @param mixed $document
     * @param User $deletedBy
     * @param bool $safe
     * @param bool $andFlush
     * @throws Exception
     */
    public function delete($document, User $deletedBy = null, $safe = true, $andFlush = true)
    {
        $id = $document->getId();
        $return = parent::delete($document, $deletedBy, $safe, $andFlush);
        $command = "board:deleted ".$id." --env=prod";
        $this->getContainer()->get('pw.event')->requestJob($command, 'high', 'sparkrebel-main', '', 'primary');
        return $return;
    }
}
