<?php

namespace PW\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface,
    PW\UserBundle\Form\Type\PartnerRegistrationFormType;

class PartnerEditFormType extends PartnerRegistrationFormType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->remove('plainPassword');
        $builder->remove('icon');
    }

    public function getName()
    {
        return 'user_partner_edit';
    }
}
