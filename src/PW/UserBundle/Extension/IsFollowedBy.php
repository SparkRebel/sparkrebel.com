<?php

namespace PW\UserBundle\Extension;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * IsFollowedBy
 */
class IsFollowedBy extends \Twig_Extension implements ContainerAwareInterface
{

    /**
     * @var ContainerInterface
     *
     * @api
     */
    protected $container;

    /**
     * Sets the Container associated with this Controller.
     *
     * @param ContainerInterface $container A ContainerInterface instance
     *
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * getFilters
     *
     * @return array of filters to apply
     */
    public function getFilters()
    {
        return array(
            'is_followed_by' => new \Twig_Filter_Method($this, 'IsFollowedBy'),
        );
    }

    /**
     * is the user following the target?
     *
     * TODO Ludicrous
     *
     * @param string $target    the user being followed (or not)
     * @param string $user      the user to get the icon for
     *
     * @return boolean yes/no
     */
     public function IsFollowedBy($target = null, $user = null)
     {
         if (!is_object($user) || !is_object($target)) {
            return null;
         }

         return $this->container->get('doctrine_mongodb.odm.document_manager')
            ->getRepository('PWUserBundle:Follow')
            ->isFollowing($user, $target);
     }

    /**
     * getName
     *
     * @return string
     */
    public function getName()
    {
        return 'pw_is_following';
    }
}
