<?php

namespace PW\BoardBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use PW\UserBundle\Repository\UserRepository;

class AdminChangeBoardOwnerType extends AbstractType
{
    /**
     * Builds the form.
     *
     * @param FormBuilder   $builder The form builder
     * @param array         $options The options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('createdBy', 'document', array(
            'class'             => 'PWUserBundle:User',
            'property'          => 'name',
            'required'          => true,
            'label'             => 'New owner',
            'empty_value'       => '-- Select new owner --',
            'query_builder' => function (UserRepository $repository) {
                return $repository->createQueryBuilder()
                    ->field('roles')->in(array('ROLE_INTERN', 'ROLE_CURATOR', 'ROLE_ADMIN'))
                    ->field('isActive')->equals(true);
            },
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'PW\BoardBundle\Document\Board',
            'validation_groups' => array('require-owner'),
        ));
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'pw_board_admin_change_board_owner';
    }
}
