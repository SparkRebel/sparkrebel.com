<?php

namespace PW\ApiBundle\Form\Type;

use FOS\OAuthServerBundle\Form\Type\AuthorizeFormType as BaseFormType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AuthorizeFormType extends BaseFormType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'PW\ApiBundle\Form\Model\Authorize'
        ));
    }
}
