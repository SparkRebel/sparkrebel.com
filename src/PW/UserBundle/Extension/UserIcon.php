<?php

namespace PW\UserBundle\Extension;

use PW\AssetBundle\Extension\AssetUrl;
use PW\UserBundle\Document\User;

/**
 * UserIcon
 */
class UserIcon extends \Twig_Extension
{
    /**
     * @return array of filters to apply
     */
    public function getFilters()
    {
        return array(
            'user_icon' => new \Twig_Filter_Method($this, 'userIcon'),
        );
    }

    /**
     * @param string $user    the user to get the icon for
     * @param string $version the version of the image to return
     *
     * @return the url for the specific version of this asset
     */
    public function userIcon($object = null, $version = null, $ensureAbsoluteUrl = false)
    {
        if (!$object) {
            return '/images/users/no-picture.png';
        }

        $icon = $object;
        if (is_object($object)) {
            if ($object instanceOf User) {
                $icon = $object->getIcon();
                if (!$icon) {
                    if ($object->getFacebookId()) {
                        return "https://graph.facebook.com/{$object->getFacebookId()}/picture?type=large";
                    } else {
                        return '/images/users/no-picture.png';
                    }
                }
            }
        }

        $assetUrl = new AssetUrl();
        return $assetUrl->version($icon, $version, $ensureAbsoluteUrl);
    }

    /**
     * getName
     *
     * @return string
     */
    public function getName()
    {
        return 'pw_user_icon';
    }
}
