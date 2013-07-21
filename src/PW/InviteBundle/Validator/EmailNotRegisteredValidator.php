<?php

namespace PW\InviteBundle\Validator;

use Symfony\Component\Validator\Constraint,
    Symfony\Component\Validator\Exception\UnexpectedTypeException,
    Symfony\Component\Validator\Exception\ConstraintDefinitionException,
    Symfony\Component\Validator\ConstraintValidator,
    Symfony\Component\DependencyInjection\ContainerAwareInterface,
    Symfony\Component\DependencyInjection\ContainerInterface,
    PW\UserBundle\Document\User;

class EmailNotRegisteredValidator extends ConstraintValidator implements ContainerAwareInterface
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
    public function isValid($email, Constraint $constraint)
    {
        $dm   = $this->container->get('doctrine_mongodb.odm.document_manager');
        $user = $dm->getRepository('PWUserBundle:User')
            ->findOneByEmail($email);

        if ($user instanceOf User) {
            $this->context->addViolation($constraint->message, array(), $email);
            return false;
        }

        return true;
    }
}
