<?php

namespace PW\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType,
    Symfony\Component\Form\FormBuilderInterface,
    PW\UserBundle\Form\Type\UserType,
    PW\UserBundle\Document\User;

class CreateUserType extends AbstractType
{
    /**
     * Builds the form.
     *
     * @param FormBuilder   $builder The form builder
     * @param array         $options The options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('user', new UserType());

        $builder->add('type', 'choice', array(
            'label'    => 'Type',
            'required' => true,
            'choices'  => array(
                'user'     => 'User',
                'brand'    => 'Brand',
                'merchant' => 'Merchant',
            ),
        ));

        $builder->add('new_icon', 'file', array(
            'label' => 'Upload Icon',
            'required' => false,
        ));
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'pw_user_create';
    }
}
