<?php

namespace PW\ApplicationBundle\Extension;


class StripEntities extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            'strip_entities' => new \Twig_Filter_Method($this, 'strip'),
        );
    }

    /**
     * @param string $text
     * @return string
     */
    public function strip($text = null)
    {
        if (empty($text)) {
            return $text;
        }

        $text = preg_replace("/&#?[a-z0-9]+;/i","",$text);
        return $text;
    }

    public function getName()
    {
        return 'strip_entities';
    }

}
