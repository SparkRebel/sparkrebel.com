<?php

namespace PW\PostBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CommentFormType extends AbstractType
{
    protected $showReplyForm;

    public function __construct($showReplyForm = 1)
    {
        $this->showReplyForm = (int) $showReplyForm;
    }

    /**
     * Builds the form.
     *
     * @param FormBuilder   $builder The form builder
     * @param array         $options The options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('content', 'textarea', array(
            'required' => false,
            'label'    => 'Add a new comment',
            'attr'     => array(
                'class'       => 'inputField',
                'placeholder' => 'Type your comment here.',
            )
        ));

        $builder->add('showReplyForm', 'hidden', array(
            'required'      => false,
            'property_path' => false,
            'data'          => $this->showReplyForm,
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'PW\PostBundle\Document\PostComment',
        ));
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'pw_post_comment';
    }
}
