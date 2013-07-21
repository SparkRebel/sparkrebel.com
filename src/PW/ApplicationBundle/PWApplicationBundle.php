<?php

namespace PW\ApplicationBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class PWApplicationBundle extends Bundle
{
    /**
     * Boots the Bundle.
     */
    public function boot()
    {
        //$this->container->get('aws_s3')->register_stream_wrapper();
    }
}
