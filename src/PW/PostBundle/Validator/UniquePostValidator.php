<?php

namespace PW\PostBundle\Validator;

use Symfony\Component\Validator\Constraint,
    Symfony\Component\Validator\Exception\UnexpectedTypeException,
    Symfony\Component\Validator\Exception\ConstraintDefinitionException,
    Symfony\Component\Validator\ConstraintValidator,
    Symfony\Component\DependencyInjection\ContainerAwareInterface,
    Symfony\Component\DependencyInjection\ContainerInterface,
    PW\PostBundle\Document\Post;

/**
 * UniquePostValidator
 */
class UniquePostValidator extends ConstraintValidator implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container object
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * If no post exists with the exact same target (only) - bail early
     * As it's not possible for there to be a duplicate.
     *
     * @param mixed $document   Post object
     * @param mixed $constraint Constraint object
     *
     * @return bool
     */
    public function isValid($document, Constraint $constraint)
    {
        /* @var $document \PW\PostBundle\Document\Post */
        if (!$document->getBoard() || !$document->getTarget()) {
            return true;
        }

        $dm         = $this->container->get('doctrine_mongodb.odm.document_manager');



        //skip documents which were already persisted. If not in edition it will always fail
        if($dm->getUnitOfWork()->getDocumentState($document) != \Doctrine\ODM\MongoDB\UnitOfWork::STATE_NEW) {
            return true;
        }


        $className  = $this->context->getCurrentClass();
        $repository = $dm->getRepository($className);

        $original = $this->findOriginal($document, $repository);
        if (!$original) {
            return true;
        }

        $duplicate = $this->findDuplicate($document, $repository);
        if (!$duplicate) {
            return true;
        }

        if ($document->getUserType() !== 'user' && $document->getBoard()->getIsSystem()) {
            $this->context->addViolation($constraint->messageDuplicate, array(), $document->getDescription());
            return false;
        }

        if ($document->equals($duplicate)) {
            $this->context->addViolation($constraint->messageDuplicate, array(), $document->getDescription());
            return false;
        }

        if($this->areThereImagesWithSamePostImageUrlAlready($document, $repository) === true) {
            $this->context->addViolation($constraint->messageDuplicate, array(), $document->getDescription());
            return false;
        }

        return true;
    }


    /**
     * As requested: we are checking on sparking the source url uniqness also in given board
     *
     * @param mixed $document
     * @param mixed $repository
     * @return boolean
     */
    public function areThereImagesWithSamePostImageUrlAlready($document, $repository)
    {
        $board_posts = $repository->findByBoard($document->getBoard())
                       ->field('image')->prime(true)
                       ->getQuery()->execute();
        foreach ($board_posts as $post) {
            if($post->getImage()->getSourceUrl() == $document->getImage()->getSourceUrl())
                return true;
        }
        return false;
    }

    /**
     * Find the last post to the same board with the same target
     * TODO why do we want that createdBy condition in there too?
     *
     * @param mixed $document   Post object
     * @param mixed $repository Repo object
     *
     * @return last post to the same board with the same target and user
     */
    protected function findDuplicate($document, $repository)
    {
        $qb = $repository->createQueryBuilder()
             ->sort('created', -1)
             ->field('board')->references($document->getBoard())
             ->field('target')->references($document->getTarget());

        if ($document->getCreatedBy()) {
            $qb->field('createdBy')->references($document->getCreatedBy());
        }

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * findOriginal
     *
     * Make sure that original points to the first post of a specific target that we know of,
     * and parent, if blank points as the very-first post too.
     *
     * @param mixed $document   Post object
     * @param mixed $repository Repo object
     *
     * @return first ever post with this target
     */
    protected function findOriginal($document, $repository)
    {
        $original = $repository->createQueryBuilder()
             ->sort('created', 1)
             ->field('target')->references($document->getTarget())
             ->getQuery()->getSingleResult();

        if (!$original) {
            return false;
        }

        if (!$document->getParent()) {
            $document->setParent($original);
        } else {
            $document->setOriginal($original);
        }

        return $original;
    }
}
