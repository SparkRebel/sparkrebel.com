<?php

namespace PW\CmsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PageType extends AbstractType
{
    /**
     * Builds the form.
     *
     * @param FormBuilder   $builder The form builder
     * @param array         $options The options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('url', 'text', array(
            'required' => true,
            'label'    => 'URL',
        ));

        $builder->add('section', 'text', array(
            'required' => false,
            'label'    => 'Section',
        ));

        $builder->add('subsection', 'text', array(
            'required' => false,
            'label'    => 'Subsection',
        ));

        $builder->add('subsectionOrder', 'number', array(
            'required' => false,
            'label'    => 'Subsection Order',
        ));

        $builder->add('title', 'text', array(
            'required' => true,
            'label'    => 'Title',
        ));

        $builder->add('content', 'textarea', array(
            'required' => false,
            'label'    => 'Content',
            'attr'     => array(
                'class' => 'tinymce',
                'data-theme' => 'advanced',
            )
        ));

        $builder->add('isActive', 'choice', array(
            'label'   => 'Active?',
            'choices' => array(
                '1' => 'Yes',
                '0' => 'No',
            )
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'PW\CmsBundle\Document\Page',
        ));
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'pw_cms_page';
    }
}
