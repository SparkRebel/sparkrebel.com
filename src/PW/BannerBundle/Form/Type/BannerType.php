<?php

namespace PW\BannerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BannerType extends AbstractType
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
            'required' => true,
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
        
        $builder->add('bannerFile', 'file', array(
            'required' => false,
            'label'    => 'Banner Image Upload',
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
               
        $builder->add('inMyStream', 'choice', array(
            'label'   => 'Show In MyStream',
            'choices' => array(
                '1' => 'Yes',
                '0' => 'No',
            )
        ));
        $builder->add('inMyBrands', 'choice', array(
            'label'   => 'Show In MyBrands',
            'choices' => array(
                '1' => 'Yes',
                '0' => 'No',
            )
        ));
        $builder->add('inMyCelebs', 'choice', array(
            'label'   => 'Show In MyCelebs',
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

        $builder->add('category', 'document', array(
            'class'             => 'PW\CategoryBundle\Document\Category',
            'property'          => 'name',
            'label'             => 'Show in Category',
            'empty_value'       => '-- Select Category --',
            //'preferred_choices' => array(0),
            'required' => false,
            'query_builder' => function ($repository) {
                return $repository->createQueryBuilder()
                    ->field('type')->equals('user')
                    ->field('isActive')->equals(true)
                    ->sort('weight', 'asc');
            },
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
            'data_class' => 'PW\BannerBundle\Document\Banner',
        ));
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'pw_banner_banner';
    }
}
