<?php

namespace PW\InviteBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CodeType extends AbstractType
{
    /**
     * @var bool
     */
    protected $admin;

    /**
     * @param bool $admin
     */
    public function __construct($admin = false)
    {
        $this->admin = (bool) $admin;
    }

    /**
     * Builds the form.
     *
     * @param FormBuilder   $builder The form builder
     * @param array         $options The options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('value', 'text', array(
            'required' => true
        ));

        $builder->add('maxUses', 'integer', array(
            'required'   => true,
            'data'       => 0,
        ));

        if ($this->admin) {
            $builder->add('assignedUser', 'document', array(
                'class'             => 'PWUserBundle:User',
                'property'          => 'name',
                'required'          => false,
                'label'             => 'Assigned to User',
                'empty_value'       => '-- Select User --',
                'preferred_choices' => array(0),
                'query_builder'     => function($repository) {
                    return $repository->createQueryBuilder()
                        ->field('type')->equals('user')
                        ->field('isActive')->equals(true)
                        ->sort('name', 'asc');
                },
            ));
        }

        $builder->add('comment', 'textarea', array(
            'required' => false
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'PW\InviteBundle\Document\Code',
        ));
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'pw_invite_code';
    }
}
