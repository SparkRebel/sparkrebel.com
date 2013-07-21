<?php

namespace PW\InviteBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UserNotAssignedCode extends Constraint
{
    public $message = 'That User already has an invite code assigned: %s';

    /**
     * The validator must be defined as a service with this name.
     *
     * @return string
     */
    public function validatedBy()
    {
        return 'validator.user.not.assigned.code';
    }

    /**
     * @see Symfony\Component\Validator\Constraint::getTargets()
     */
    public function getTargets()
    {
        return Constraint::PROPERTY_CONSTRAINT;
    }
}