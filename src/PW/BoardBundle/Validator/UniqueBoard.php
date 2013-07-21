<?php

namespace PW\BoardBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UniqueBoard extends Constraint
{
    public $message = 'A Collection already exists with this name.';

    /**
     * The validator must be defined as a service with this name.
     *
     * @return string
     */
    public function validatedBy()
    {
        return 'validator.unique.board';
    }

    /**
     * @see Symfony\Component\Validator\Constraint::getTargets()
     */
    public function getTargets()
    {
        return Constraint::CLASS_CONSTRAINT;
    }
}