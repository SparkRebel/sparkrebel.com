<?php

namespace PW\ApiBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class PWApiBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSOAuthServerBundle';
    }
}
