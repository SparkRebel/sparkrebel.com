<?php

namespace PW\UserBundle\Form\Type\User\Settings;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use PW\UserBundle\Document\User\Settings\Email as EmailSettings;

class EmailFormType extends AbstractType
{
    /**
     * Builds the form.
     *
     * @param FormBuilder $builder The form builder
     * @param array       $options The options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('notificationFrequency', 'choice', array(
            'choices' => array(
                EmailSettings::FREQUENCY_NEVER => 'Never',
                EmailSettings::FREQUENCY_ASAP  => 'Immediately',
            ),
            'label'    => 'How often do you want to be notified by email?',
            'required' => true,
        ));

        $builder->add('notificationTypes', 'collection' , array(
            'type' => 'checkbox',
            'required' => false,
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'PW\UserBundle\Document\User\Settings\Email',
        ));
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'pw_user_settings_email';
    }
}
