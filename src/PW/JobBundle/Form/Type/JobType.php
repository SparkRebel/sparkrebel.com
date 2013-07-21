<?php

namespace PW\JobBundle\Form\Type;

use PW\UserBundle\Repository\UserRepository;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class JobType extends AbstractType
{
    /**
     * Builds the form.
     *
     * @param FormBuilder   $builder The form builder
     * @param array         $options The options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('cmd', 'textarea', array(
            'required' => true,
            'label'    => 'Cmd',
            'attr'     => array(
                'class' => 'cmd',
                'style'=>'font-family:monospace;'
            )
        ));
        
        $builder->add('keywords', 'textarea', array(
            'required' => true,
            'label'    => 'Keywords',
            'attr'     => array(
                'class' => 'keywords',
                'style'=>'font-family:monospace;'
            )
        ));
        
        //$builder->add('collection', 'text', array(
        //    'required' => true,
        //    'label'    => 'Collection',
        //));
        
        
        $builder->add('user', 'document', array(
            'class'             => 'PWUserBundle:User',
            'property'          => 'username',
            'required'          => true,
            'label'             => 'User',
            'empty_value'       => '-- Select User --',
            'preferred_choices' => array(0),
            'query_builder'     => function(UserRepository $repository) {
                return $repository->createQueryBuilder()
                    ->field('email')->equals("admin@sparkrebel.com") //show only System user (see ticket #307)
                    //->field('roles')->in(array('ROLE_CURATOR','ROLE_ADMIN','ROLE_PARTNER'))
                    ->field('isActive')->equals(true)
                    ->sort('username', 'asc')
                    ;
            },
        ));
        
        $builder->add('startDate', 'date', array(
            'input'  => 'datetime',
            'widget' => 'single_text',
            'format' => 'yyyy-MM-dd HH:mm:ss',
        ));
        
        $builder->add('endDate', 'date', array(
            'input'  => 'datetime',
            'widget' => 'single_text',
            'format' => 'yyyy-MM-dd HH:mm:ss',
        ));
        
        $builder->add('isActive', 'choice', array(
            'label'   => 'Is Active',
            'choices' => array(
                '1' => 'Yes',
                '0' => 'No',
            )
        ));
 
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'PW\JobBundle\Document\Job',
        ));
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'pw_job_job';
    }
}
