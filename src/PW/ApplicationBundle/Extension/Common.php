<?php

namespace PW\ApplicationBundle\Extension;

class Common extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            'http_build_query' => new \Twig_Filter_Function('http_build_query'),
            'url_decode'       => new \Twig_Filter_Function('urldecode'),
            'long_to_ip'       => new \Twig_Filter_Function('long2ip'),
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'common_twig_extension';
    }
}
