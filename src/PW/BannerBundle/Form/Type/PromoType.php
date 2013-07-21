<?php

namespace PW\BannerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use PW\UserBundle\Repository\UserRepository;

class PromoType extends AbstractType
{
    /**
     * Builds the form.
     *
     * @param FormBuilder   $builder The form builder
     * @param array         $options The options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('description', 'textarea', array(
            'required' => false,
            'label'    => 'Description',
            'attr'     => array(
                'class' => 'description',
                'style'=>'font-family:monospace;'
            )
        ));
        
        $builder->add('url', 'text', array(
            'required' => true,
            'label'    => 'Destination Url',
            'attr'     => array(
                'class' => 'url',
                'style'=>'font-family:monospace;'
            )
        ));
        
        $builder->add('isUrlTargetBlank', 'choice', array(
            'label'   => 'Open in new window',
            'choices' => array(
                '1' => 'Yes',
                '0' => 'No',
            )
        ));
        
        $builder->add('bannerFile', 'file', array(
            'required' => false,
            'label'    => 'Promo Image Upload',
            'property_path' => null,
            'attr'     => array(
                'class' => 'bannerFile',
                'style'=>'font-family:monospace;'
            )
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
        
        $builder->add('inMyBrands', 'choice', array(
            'label'   => 'Show In MyBrands',
            'choices' => array(
                '1' => 'Yes',
                '0' => 'No',
            )
        ));
        
        $builder->add('user', 'document', array(
            'class'             => 'PWUserBundle:User',
            'property'          => 'username',
            'required'          => false,
            'label'             => 'Brand or Merchant',
            'empty_value'       => '-- Select Brand or Merchant --',
            //'preferred_choices' => array(0),
            'query_builder'     => function(UserRepository $repository) {
                return $repository->createQueryBuilder()
                    //->field('email')->equals("admin@sparkrebel.com") //show only System user (see ticket #307)
                    //->field('roles')->in(array('ROLE_PARTNER'))
                    ->field('type')->in(array('brand','merchant'))
                    ->field('isActive')->equals(true)
                    ->sort('username', 'asc')
                    ;
            },
        ));

        $builder->add('inShop', 'choice', array(
            'label'   => 'Show In Shop(in Sales&Promos)',
            'choices' => array(
                '1' => 'Yes',
                '0' => 'No',
            )
        ));
        
        $builder->add('inMyStream', 'choice', array(
            'label'   => 'Show In MyStream',
            'choices' => array(
                '1' => 'Yes',
                '0' => 'No',
            )
        ));

        $builder->add('inAllCategories', 'choice', array(
            'label'   => 'Show In AllCategories',
            'choices' => array(
                '1' => 'Yes',
                '0' => 'No',
            )
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
            'data_class' => 'PW\BannerBundle\Document\Promo',
        ));
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'pw_promo_promo';
    }
}
