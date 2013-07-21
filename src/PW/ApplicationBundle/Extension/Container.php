<?php

namespace PW\ApplicationBundle\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;

class Container extends \Twig_Extension
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
            'container_param' => new \Twig_Function_Method($this, 'getParameter'),
        );
    }

    /**
     * @param string $key
     * @return string
     */
    public function getParameter($key)
    {
        return $this->container->getParameter($key);
    }

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'container';
    }
}
