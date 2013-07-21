<?php

namespace PW\BoardBundle\Validator;

use Symfony\Component\Validator\Constraint,
    Symfony\Component\Validator\Exception\UnexpectedTypeException,
    Symfony\Component\Validator\Exception\ConstraintDefinitionException,
    Symfony\Component\Validator\ConstraintValidator,
    Symfony\Component\DependencyInjection\ContainerAwareInterface,
    Symfony\Component\DependencyInjection\ContainerInterface,
    PW\BoardBundle\Document\Board;

class UniqueBoardValidator extends ConstraintValidator implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @return bool
     */
    public function isValid($document, Constraint $constraint)
    {
        /* @var $document \PW\BoardBundle\Document\Board */
        if (!$document->getCategory() || !$document->getCreatedBy()) {
            return true;
        }

        $dm         = $this->container->get('doctrine_mongodb.odm.document_manager');
        $className  = $this->context->getCurrentClass();
        $repository = $dm->getRepository($className);

        $qb = $repository->createQueryBuilder()
            ->field('name')->equals($document->getName())
            ->field('category')->references($document->getCategory())
            ->field('createdBy')->references($document->getCreatedBy())
            ->field('isActive')->equals(true)
            ->limit(1);

        $result = $qb->getQuery()->execute();

        if ($result->count() == 0) {
            return true;
        }

        if ($result->count() == 1) {
            foreach ($result as $board /* @var $board \PW\BoardBundle\Document\Board */) {
                if ($document->getId() == $board->getId()) {
                    return true;
                }
            }
        }

        $this->context->addViolation($constraint->message, array(), $document->getName());
        return false;
    }
}
