<?php

namespace PW\ApplicationBundle\Document\Exception;

use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\ConstraintViolationList;

class ConstraintViolationException extends ValidatorException
{
    const SINGLE_VIOLATION    = 1;
    const MULTIPLE_VIOLATIONS = 2;

    /**
     * @var \Symfony\Component\Validator\ConstraintViolationList
     */
    protected $violationList;

    /**
     * @param ConstraintViolationList|string $message
     * @param int $code
     * @param Exception $previous
     */
    public function __construct($message = null, $code = null, $previous = null)
    {
        if (is_object($message) && $message instanceOf ConstraintViolationList) {
            $this->violationList = $message;
            if ($this->violationList->count() == 1) {
                foreach ($this->violationList as $violation /* @var $violation \Symfony\Component\Validator\ConstraintViolation */) {
                    $root    = $violation->getRoot();
                    $class   = is_object($root) ? get_class($root) : $root;
                    $message = "{$class}.{$violation->getPropertyPath()}: {$violation->getMessage()}";
                    break;
                }
                if (empty($code)) {
                    $code = self::SINGLE_VIOLATION;
                }
            } else {
                $message = 'Multiple constraint violations occurred';
                if (empty($code)) {
                    $code = self::MULTIPLE_VIOLATIONS;
                }
            }
        }

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return \Symfony\Component\Validator\ConstraintViolationList
     */
    public function getViolations()
    {
        return $this->violationList;
    }
}