<?php

namespace PW\InviteBundle\Validator;

use Symfony\Component\Validator\Constraint,
    Symfony\Component\Validator\Exception\UnexpectedTypeException,
    Symfony\Component\Validator\Exception\ConstraintDefinitionException,
    Symfony\Component\Validator\ConstraintValidator,
    Symfony\Component\DependencyInjection\ContainerAwareInterface,
    Symfony\Component\DependencyInjection\ContainerInterface,
    PW\UserBundle\Document\User;

class CodeIsValidValidator extends ConstraintValidator implements ContainerAwareInterface
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
    public function isValid($value, Constraint $constraint)
    {
        /* @var $codeManager \PW\InviteBundle\Model\CodeManager */
        $codeManager = $this->container->get('pw_invite.code_manager');

        /* @var $code \PW\InviteBundle\Document\Code */
        $code = $codeManager->findByValue($value);

        if (!$code) {
            $this->context->addViolation("You've entered an invalid code.", array(), $value);
            return false;
        }

        if ($code->getUsesLeft() !== null) {
            if ($code->getUsesLeft() <= 0) {
                $this->context->addViolation($constraint->message, array(), $value);
                return false;
            }
        }

        return true;
    }
}