<?php

namespace PW\BoardBundle\Form\Type;

use Symfony\Component\Form\AbstractType,
    Symfony\Component\Form\FormBuilderInterface,
    PW\BoardBundle\Form\Type\BoardType;

class CreateBoardType extends AbstractType
{
    /**
     * Builds the form.
     *
     * @param FormBuilder   $builder The form builder
     * @param array         $options The options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('board', new BoardType());
    }


    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'pw_board_create';
    }

    /*public function getDefaultOptions(array $options)
    {
        return array(            
            /'csrf_protection' => false,                                    
        );
    }*/
}
