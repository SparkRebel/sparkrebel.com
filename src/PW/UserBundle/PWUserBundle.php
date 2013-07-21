<?php

namespace PW\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class PWUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
