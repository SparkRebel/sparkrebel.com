<?php

namespace PW\PostBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use PW\BoardBundle\Form\Type\AddBoardType;
use PW\UserBundle\Document\User;
use PW\CategoryBundle\Repository\CategoryRepository;

class EditPostType extends PostType
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
        
        if (in_array($this->user->getType(), array('brand','merchant'))) {
            // when created by brand/merchant -> disable some fields
            $builder->get('category')->setDisabled(true);
            $builder->get('description')->setDisabled(true);
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'PW\PostBundle\Document\Post',
        ));
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'pw_edit_post';
    }
}
