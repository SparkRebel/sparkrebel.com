<?php

namespace PW\BoardBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use PW\CategoryBundle\Repository\CategoryRepository;
use PW\CategoryBundle\Document\Category;

class BoardType extends AbstractType
{
    /**
     * Builds the form.
     *
     * @param FormBuilder   $builder The form builder
     * @param array         $options The options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', array(
            'required'  => true,
            'label'     => 'Collection Name',
        ));

        $builder->add('category', 'document', array(
            'class'             => 'PW\CategoryBundle\Document\Category',
            'property'          => 'name',
            'required'          => true,
            'label'             => 'Category',
            'empty_value'       => '-- Select a Category --',
            'query_builder' => function (CategoryRepository $repository) {
                return $repository->createQueryBuilder()
                    ->field('type')->equals('user')
                    ->field('isActive')->equals(true)
                    ->sort('weight', 'asc');
            },
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'PW\BoardBundle\Document\Board',
            'validation_groups' => array('Default', 'require-category'),
        ));
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'pw_board';
    }
}
