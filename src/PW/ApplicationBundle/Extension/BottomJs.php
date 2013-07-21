<?php

namespace PW\ApplicationBundle\Extension;

class BottomJs extends \Twig_Extension
{
    /** @var array */
    private $_js = array();

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            'put_js'   => new \Twig_Function_Method($this, 'putJs', array('is_safe' => array('html'))),
            'print_js' => new \Twig_Function_Method($this, 'printJs', array('is_safe' => array('html')))
        );
    }

    /**
     * @param string $js
     */
    public function putJs($js)
    {
        $this->_js[] = $js;
    }

    /**
     * @return string
     */
    public function printJs()
    {
        return implode(PHP_EOL, $this->_js);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'bottom_js_twig_extension';
    }
}
