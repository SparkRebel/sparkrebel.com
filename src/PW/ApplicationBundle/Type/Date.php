<?php

namespace PW\ApplicationBundle\Type;

use Doctrine\ODM\MongoDB\Mapping\Types\Type;

class Date extends Type
{
    public function convertToDatabaseValue($value)
    {
        $time = strtotime($value);
        return array(
            'year'  => date('Y', $time),
            'month' => date('m', $time),
            'day'   => date('d', $time),
        );
    }
}
