<?php

namespace PW\Boardbundle\Extension;

use PW\AssetBundle\Extension\AssetUrl;
use PW\BoardBundle\Document\Board;

/**
 * boardIcon
 */
class BoardIcon extends \Twig_Extension
{
    /**
     * @return array of filters to apply
     */
    public function getFilters()
    {
        return array(
            'board_icon' => new \Twig_Filter_Method($this, 'boardIcon'),
        );
    }

    /**
     * @param string $board    the board to get the icon for
     * @param string $version the version of the image to return
     *
     * @return the url for the specific version of this asset
     */
    public function boardIcon($object = null, $version = null, $ensureAbsoluteUrl = false)
    {
        if (!$object) {
            return '/images/users/no-picture.png';
        }

        $icon = $object;
        if (is_object($object)) {
            if ($object instanceOf Board) {
                $icon = $object->getIcon();
                if (!$icon) {                    
                    return '/images/users/no-picture.png';                    
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
        return 'pw_board_icon';
    }
}
