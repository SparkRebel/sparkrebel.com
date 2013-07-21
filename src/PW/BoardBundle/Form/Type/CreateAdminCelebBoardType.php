<?php

namespace PW\BoardBundle\Form\Type;

use Symfony\Component\Form\AbstractType,
    Symfony\Component\Form\FormBuilderInterface,
    PW\BoardBundle\Form\Type\AdminCelebBoardType;

class CreateAdminCelebBoardType extends AbstractType
{
    /**
     * Builds the form.
     *
     * @param FormBuilder   $builder The form builder
     * @param array         $options The options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('board', new AdminCelebBoardType());
        
        $builder->add('new_icon', 'file', array(
            'label' => 'Upload New Icon',
            'required' => false,
        ));
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'pw_admin_celeb_board_create';
    }
}
