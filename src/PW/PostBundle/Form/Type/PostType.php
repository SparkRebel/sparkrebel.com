<?php

namespace PW\PostBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use PW\BoardBundle\Form\Type\AddBoardType;
use PW\UserBundle\Document\User;
use PW\CategoryBundle\Repository\CategoryRepository;

class PostType extends AbstractType
{
    /**
     * @var \PW\UserBundle\Document\User
     */
    protected $user;

    /**
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Builds the form.
     *
     * @param FormBuilder   $builder The form builder
     * @param array         $options The options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $this->user;

        $builder->add('board', 'document', array(
            'class'             => 'PW\BoardBundle\Document\Board',
            'property'          => 'name',
            'required'          => true,
            'label'             => 'Collection',
            'empty_value'       => '-- Select Collection --',
            'query_builder'     => function($repository) use ($user) {
                return $repository->createQueryBuilderWithOptions()
                    ->field('createdBy')->references($user)
                    ->sort('name', 'asc');
            },
        ));

        $builder->add('category', 'document', array(
            'class'             => 'PW\CategoryBundle\Document\Category',
            'property'          => 'name',
            'required'          => true,
            'label'             => 'Publish for',
            'empty_value'       => '-- Select Category --',
            'query_builder' => function ($repository) {
                return $repository->createQueryBuilder()
                    ->field('type')->equals('user')
                    ->field('isActive')->equals(true)
                    ->sort('weight', 'asc');
            },
        ));

        $builder->add('description', 'textarea', array(
            'required' => true,
            'label'    => 'Description',
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'PW\PostBundle\Document\Post',
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
        return 'pw_post';
    }
}
