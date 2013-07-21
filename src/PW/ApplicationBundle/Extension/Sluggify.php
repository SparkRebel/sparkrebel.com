<?php

namespace PW\ApplicationBundle\Extension;

use Gedmo\Sluggable\Util\Urlizer;

class Sluggify extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            'sluggify' => new \Twig_Filter_Method($this, 'sluggify'),
        );
    }

    /**
     * @param string $text
     * @return string
     */
    public function sluggify($text = null)
    {
        if (empty($text)) {
            return $text;
        }

        return $this->truncate(
            Urlizer::urlize(
                Urlizer::transliterate($text)
            )
        );
    }

    public function getName()
    {
        return 'sluggify';
    }

    /**
     * truncates a text to given character width, without chopping up words
     *
     * @param string $text
     * @param int $width
     * @return string
     */
    public function truncate($text, $width = 150)
    {
        if (strlen($text) > $width)
        {
            $text = wordwrap($text, $width);
            $text = substr($text, 0, strpos($text, "\n"));
        }

        return $text;
    }
}
