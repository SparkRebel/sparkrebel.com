<?php

namespace PW\ApplicationBundle\Model;

interface ManagerInterface
{
    /**
     * Creates an empty instance.
     *
     * @param array $object
     */
    function create(array $data = array());

    /**
     * Returns the user's fully qualified class name.
     *
     * @return string
     */
    function getClass();
}
