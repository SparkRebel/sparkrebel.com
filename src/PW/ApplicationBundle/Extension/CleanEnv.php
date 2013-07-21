<?php

namespace PW\ApplicationBundle\Extension;

class CleanEnv extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            'clean_env' => new \Twig_Filter_Method($this, 'cleanEnv'),
        );
    }

    /**
     * @param string $text
     * @return string
     */
    public function cleanEnv($url = null)
    {
        return preg_replace('/app.*\.php\//', '', $url, 1);
    }

    public function getName()
    {
        return 'clean_env';
    }

}
