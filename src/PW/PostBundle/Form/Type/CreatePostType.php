<?php

namespace PW\PostBundle\Form\Type;

use Symfony\Component\Form\AbstractType,
    Symfony\Component\Form\FormBuilderInterface,
    PW\PostBundle\Form\Type\PostType,
    PW\UserBundle\Document\User;

class CreatePostType extends AbstractType
{
    /**
     * @var \PW\UserBundle\Document\User
     */
    protected $user;

    /**
     * @var bool
     */
    protected $multiple;

    /**
     * @var bool
     */
    protected $postOnFacebook;

    /**
     * @param User $user
     */
    public function __construct(User $user, $multiple = false, $postOnFacebook = true)
    {
        $this->user           = $user;
        $this->multiple       = (bool) $multiple;
        $this->postOnFacebook = (bool) $postOnFacebook;
    }

    /**
     * Builds the form.
     *
     * @param FormBuilder   $builder The form builder
     * @param array         $options The options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('post', new PostType($this->user));

        if ($this->multiple) {
            $builder->get('post')->remove('description');
        }
        
        $options = array(
            'label'    => 'Post on Facebook',
            'required' => false,
        );
        if ($this->postOnFacebook) {
            $options['attr'] = array('checked'   => 'checked');
        }
        $builder->add('post_on_facebook', 'checkbox', $options);  
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'pw_post_create';
    }
}
