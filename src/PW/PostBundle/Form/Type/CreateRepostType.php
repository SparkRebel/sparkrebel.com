<?php

namespace PW\PostBundle\Form\Type;

use Symfony\Component\Form\AbstractType,
    Symfony\Component\Form\FormBuilderInterface,
    PW\PostBundle\Form\Type\RepostType,
    PW\UserBundle\Document\User;

class CreateRepostType extends AbstractType
{
    /**
     * @var \PW\UserBundle\Document\User
     */
    protected $user;
 

    /**
     * @var bool
     */
    protected $postOnFacebook;

    /**
     * @param User $user
     */
    public function __construct(User $user, $postOnFacebook = true)
    {
        $this->user           = $user;
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
        $builder->add('post', new RepostType($this->user));


        $builder->add('post_on_facebook', 'checkbox', array(
            'label'    => 'Post on Facebook',
            'required' => false,
        ));

        if ($this->postOnFacebook) {
            $builder->get('post_on_facebook')->setAttribute('attr', array('checked' => 'checked'));
        }
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'pw_repost_create';
    }
}
