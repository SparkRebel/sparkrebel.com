<?php

namespace PW\UserBundle\Extension;

use PW\AssetBundle\Extension\AssetUrl;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * ProfileLink
 */
class ProfileLink extends \Twig_Extension
{
    
    private $generator;
    
    public function setGenerator(UrlGeneratorInterface $generator)
    {
        $this->generator = $generator;
    }    
    
    /**
     * getFilters
     *
     * @return array of filters to apply
     */
    public function getFilters()
    {
        return array(
            'profile_link' => new \Twig_Filter_Method($this, 'profileLink'),
        );
    }

    /**
     * get the url to the user's profile
     *
     *
     * @param string $user    the user to get the icon for
     *
     * @return the url for the user's profile
     */
    public function profileLink($user = null, $absolute = false)
    {
        if (!is_object($user)) {
            return null;
        }
        
        if ($user->getType() != 'user') {
            return $this->generator->generate('pw_user_brand_view', array('slug' => $user->getUsername()), $absolute);
        }

        return $this->generator->generate('user_profile_view', array('name' => $user->getName()), $absolute);
    }

    /**
     * getName
     *
     * @return string
     */
    public function getName()
    {
        return 'pw_profile_link';
    }
}
