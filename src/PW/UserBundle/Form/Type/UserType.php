<?php

namespace PW\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use PW\UserBundle\Document\Brand;
class UserType extends AbstractType
{
    /**
     * @var bool
     */
    protected $admin = false;

    protected $user;

    /**
     * @param bool $admin
     */
    public function __construct($admin = false, $user = null)
    {
        $this->admin = (bool) $admin;
        $this->user = $user;
    }

    /**
     * Builds the form.
     *
     * @param FormBuilder   $builder The form builder
     * @param array         $options The options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', array(
            'label'    => 'Name',
            'required' => true,
        ));

        $builder->add('email', 'email', array(
            'label'    => 'E-mail',
            'required' => false,
        ));

        if ($this->admin) {
            $builder->add('username', 'text', array(
                'label'    => 'Username',
                'required' => false,
            ));
        }

        $builder->add('plainPassword', 'repeated', array(
            'required'    => false,
            'type'        => 'password',
            'first_name'  => 'password',
            'second_name' => 'password_confirm',
        ));

        $builder->add('about', 'textarea', array(
            'label'    => 'About',
            'required' => false,
        ));

        $builder->add('websiteUrl', 'url', array(
            'label'    => 'Website URL',
            'required' => false,
        ));

        $builder->add('websiteTitle', 'text', array(
            'label'    => 'Website Title',
            'required' => false,
        ));

        if ($this->admin) {
            if($this->user && $this->user instanceof Brand) {
                $builder->add('alias', 'textarea', array(
                    'label' => 'User Alias',
                    'required' => false,
                ));
            }
            

            $builder->add('roles', 'choice', array(
                'label'    => 'Roles',
                'required' => false,
                'multiple' => true,
                'choices'  => array(
                    'ROLE_INTERN'  => 'ROLE_INTERN',
                    'ROLE_CURATOR' => 'ROLE_CURATOR',
                    'ROLE_PARTNER' => 'ROLE_PARTNER',
                    'ROLE_ADMIN'   => 'ROLE_ADMIN',
                    'ROLE_OPERATOR'   => 'ROLE_OPERATOR',
                ),
            ));
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'PW\UserBundle\Document\User',
        ));
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'pw_user';
    }
}
