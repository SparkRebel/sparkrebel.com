<?php

namespace PW\BoardBundle\Form\Type;

use Symfony\Component\Form\AbstractType,
    Symfony\Component\Form\FormBuilderInterface,
    PW\CategoryBundle\Repository\CategoryRepository;

class CollectionEditBoardType extends BoardType
{
    /**
     * Builds the form.
     *
     * @param FormBuilder   $builder The form builder
     * @param array         $options The options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('description', 'textarea', array(
             'required'  => false,
             'label'     => 'Description',
        ));
    }


}
