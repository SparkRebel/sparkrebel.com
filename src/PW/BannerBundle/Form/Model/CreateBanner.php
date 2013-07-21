<?php

namespace PW\BannerBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert,
    PW\BannerBundle\Document\Banner;

class CreateBanner
{
    /**
     * @Assert\Type(type="PW\BannerBundle\Document\Banner")
     * @Assert\Valid
     */
    protected $banner;

    /**
     * @param Banner $banner
     */
    public function __construct(Banner $banner = null)
    {
        $this->banner = $banner;
    }

    /**
     * @param Banner $banner
     */
    public function setBanner(Banner $banner)
    {
        $this->banner = $banner;
    }

    /**
     * @return \PW\BannerBundle\Document\Banner
     */
    public function getBanner()
    {
        return $this->banner;
    }
}
