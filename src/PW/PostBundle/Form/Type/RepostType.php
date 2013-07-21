<?php

namespace PW\PostBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use PW\BoardBundle\Form\Type\AddBoardType;
use PW\UserBundle\Document\User;
use PW\CategoryBundle\Repository\CategoryRepository;

class RepostType extends PostType
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
        $user = $this->user;

        $builder->remove('category');
        $builder->remove('board');
        $builder->add('board', 'document', array(
            'class'             => 'PW\BoardBundle\Document\Board',
            'property'          => 'name',
            'required'          => true,
            'label'             => 'Collection',
            'query_builder'     => function($repository) use ($user) {
                return $repository->createQueryBuilderWithOptions()
                    ->field('createdBy')->references($user)
                    ->sort('name', 'asc');
            },
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'PW\PostBundle\Document\Post'
        ));
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'pw_repost';
    }
}
