<?php
namespace PW\FlagBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository,
    PW\UserBundle\Document\User,
    PW\PostBundle\Document\Post,
    PW\PostBundle\Document\PostComment;

class FlagRepository extends DocumentRepository
{
    /**
     * @param string $targetType
     * @param string $reasonType
     * @param string $status
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function findByTargetType($targetType, $reasonType = null, $status = null)
    {
        $qb = $this->createQueryBuilder()
            ->sort('created', 'desc');

        switch ($targetType) {
            case 'posts':
                $qb->field('target.$ref')->equals('posts');
                break;
            case 'posts_activity':
            case 'comments':
                $qb->field('target.$ref')->equals('posts_activity');
                break;
            default:
                throw new \Exception(sprintf("Invalid flagged object type '%s'", $targetType));
                break;
        }

        switch ($reasonType) {
            case 'copyright':
            case 'inappropriate':
            case 'other':
                $qb->field('type')->equals($reasonType);
                break;
        }

        switch ($status) {
            case 'pending':
            case 'approved':
            case 'rejected':
                $qb->field('status')->equals($status);
                break;
        }

        return $qb;
    }

    /**
     * @param \PW\UserBundle\Document\User $user
     * @param int $limit
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function findByCreatedUser(User $user, $limit = null)
    {
        $qb = $this->createQueryBuilder()
            ->field('createdBy')->references($user)
            ->sort('created', 'desc');

        if (!empty($limit)) {
            $qb->limit((int) $limit);
        }

        return $qb;
    }

    /**
     * @param \PW\UserBundle\Document\User $user
     * @param int $limit
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function findByTargetUser(User $user, $limit = null)
    {
        $qb = $this->createQueryBuilder()
            ->field('targetUser')->references($user)
            ->sort('created', 'desc');

        if (!empty($limit)) {
            $qb->limit((int) $limit);
        }

        return $qb;
    }

    /**
     * @param mixed $object
     * @param int $limit
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function findByTarget($object, $limit = null)
    {
        $qb = $this->createQueryBuilder()
            ->field('target')->references($object)
            ->sort('created', 'desc');

        if (!empty($limit)) {
            $qb->limit((int) $limit);
        }

        return $qb;
    }

    /**
     * @param \PW\PostBundle\Document\Post $post
     * @param int $limit
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function findByTargetPost(Post $post, $limit = null)
    {
        return $this->findByTarget($post, $limit);
    }

    /**
     * @param \PW\PostBundle\Document\PostComment $comment
     * @param int $limit
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function findByTargetComment(Comment $comment, $limit = null)
    {
        return $this->findByTarget($comment, $limit);
    }

    /**
     * @param int $ip
     * @param int $limit
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function findByIpAddress($ip, $limit = null)
    {
        $qb = $this->createQueryBuilder()
            ->field('ip')->equals($ip)
            ->sort('created', 'desc');

        if (!empty($limit)) {
            $qb->limit((int) $limit);
        }

        return $qb;
    }
}
