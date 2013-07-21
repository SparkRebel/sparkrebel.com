<?php

namespace PW\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;

class RegistrationFormType extends BaseType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', array(
            'label'    => 'Name',
            'required' => true,
        ));

        $builder->add('email', 'email', array(
            'label'    => 'E-mail',
            'required' => true,
        ));

        $builder->add('plainPassword', 'repeated', array(
            'required'    => true,
            'type'        => 'password',
            'first_name'  => 'password',
            'second_name' => 'password_confirm',
        ));
    }

    public function getName()
    {
        return 'pw_user_registration';
    }
}
