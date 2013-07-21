<?php

namespace PW\InviteBundle\Validator;

use Symfony\Component\Validator\Constraint,
    Symfony\Component\Validator\Exception\UnexpectedTypeException,
    Symfony\Component\Validator\Exception\ConstraintDefinitionException,
    Symfony\Component\Validator\ConstraintValidator;

class UserNotAssignedCodeValidator extends ConstraintValidator
{
    /**
     * @return bool
     */
    public function isValid($user, Constraint $constraint)
    {
        if ($user && $assignedCode = $user->getAssignedInviteCode()) {
            $this->context->addViolation(sprintf($constraint->message, $assignedCode->getValue()), array(), $user);
            return false;
        }

        return true;
    }
}