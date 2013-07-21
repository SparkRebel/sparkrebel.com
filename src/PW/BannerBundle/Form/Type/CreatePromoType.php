<?php

namespace PW\BannerBundle\Form\Type;

use Symfony\Component\Form\AbstractType,
    Symfony\Component\Form\FormBuilderInterface,
    PW\BannerBundle\Form\Type\PromoType;

class CreatePromoType extends AbstractType
{
    /**
     * Builds the form.
     *
     * @param FormBuilder   $builder The form builder
     * @param array         $options The options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('promo', new PromoType());
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'pw_promo_promo_create';
    }
}
