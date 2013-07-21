<?php

namespace PW\InviteBundle\Security\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class InviteOnlyException extends AuthenticationException
{
    
}