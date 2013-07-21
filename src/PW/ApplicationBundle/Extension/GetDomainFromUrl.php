<?php

namespace PW\ApplicationBundle\Extension;

class GetDomainFromUrl extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            'get_domain_from_url' => new \Twig_Filter_Method($this, 'getDomainFromUrl'),
        );
    }

    /**
     * @param string $url
     * @return string
     */
    public function getDomainFromUrl($url = null)
    {
        $parse = parse_url($url);
        return isset($parse['host']) ? $parse['host'] : $this->_truncate($url, 20);
    }

    /**
     * @param string $string
     * @param int $limit
     * @param string $break
     * @param string $pad
     * @return string
     */
    protected function _truncate($string = null, $limit = 25, $break = ' ', $pad = '&hellip;')
    {
        if (strlen($string) <= $limit) {
            return $string;
        }

        if (false !== ($breakpoint = strpos($string, $break, $limit))) {
            if ($breakpoint < strlen($string) - 1) {
                $string = substr($string, 0, $breakpoint) . $pad;
            }
        }

        return $string;
    }

    public function getName()
    {
        return 'pw_get_domain_from_url';
    }
}
