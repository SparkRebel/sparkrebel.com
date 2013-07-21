<?php

namespace PW\UserBundle\Form\Type\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use PW\UserBundle\Form\Type\User\Settings\EmailFormType;

class SettingsFormType extends AbstractType
{
    /**
     * Builds the form.
     *
     * @param FormBuilder $builder The form builder
     * @param array       $options The options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', new EmailFormType());
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'PW\UserBundle\Document\User\Settings',
        ));
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'pw_user_settings';
    }
}
