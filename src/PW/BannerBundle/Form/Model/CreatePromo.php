<?php

namespace PW\BannerBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert,
    PW\BannerBundle\Document\Promo;

class CreatePromo
{
    /**
     * @Assert\Type(type="PW\BannerBundle\Document\Promo")
     * @Assert\Valid
     */
    protected $promo;

    /**
     * @param Promo $promo
     */
    public function __construct(Promo $promo = null)
    {
        $this->promo = $promo;
    }

    /**
     * @param Promo $promo
     */
    public function setPromo(Promo $promo)
    {
        $this->promo = $promo;
    }

    /**
     * @return \PW\BannerBundle\Document\Promo
     */
    public function getPromo()
    {
        return $this->promo;
    }
}
