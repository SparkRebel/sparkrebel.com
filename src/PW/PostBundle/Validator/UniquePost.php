<?php

namespace PW\PostBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UniquePost extends Constraint
{
    public $messageDuplicate = 'Seems you already sparked the exact same post on this board';
    public $messageTooSoon = 'Seems you already sparked this to the same board just now';

    /**
     * The validator must be defined as a service with this name.
     *
     * @return string
     */
    public function validatedBy()
    {
        return 'validator.unique.post';
    }

    /**
     * @see Symfony\Component\Validator\Constraint::getTargets()
     */
    public function getTargets()
    {
        return Constraint::CLASS_CONSTRAINT;
    }
}
