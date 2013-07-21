<?php

namespace PW\InviteBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class CodeIsValid extends Constraint
{
    public $message = 'Oops! It seems that invite code has expired... If you have another one, enter it below...';

    /**
     * The validator must be defined as a service with this name.
     *
     * @return string
     */
    public function validatedBy()
    {
        return 'validator.code.is.valid';
    }

    /**
     * @see Symfony\Component\Validator\Constraint::getTargets()
     */
    public function getTargets()
    {
        return Constraint::PROPERTY_CONSTRAINT;
    }
}