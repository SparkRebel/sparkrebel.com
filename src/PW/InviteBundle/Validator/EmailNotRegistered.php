<?php

namespace PW\InviteBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class EmailNotRegistered extends Constraint
{
    public $message = 'A User already exists with this e-mail.';

    /**
     * The validator must be defined as a service with this name.
     *
     * @return string
     */
    public function validatedBy()
    {
        return 'validator.email.not.registered';
    }

    /**
     * @see Symfony\Component\Validator\Constraint::getTargets()
     */
    public function getTargets()
    {
        return Constraint::PROPERTY_CONSTRAINT;
    }
}