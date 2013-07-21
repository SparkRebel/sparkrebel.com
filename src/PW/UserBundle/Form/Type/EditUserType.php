<?php

namespace PW\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use PW\UserBundle\Form\Type\UserType;
use PW\UserBundle\Document\User;

class EditUserType extends AbstractType
{
    /**
     * @var \PW\UserBundle\Document\User
     */
    protected $user;

    /**
     * @var bool
     */
    protected $admin = false;

    /**
     * @param \PW\UserBundle\Document\User $user
     */
    public function __construct(User $user, $admin = false)
    {
        $this->user  = $user;
        $this->admin = !$admin ? $user->hasRole('ROLE_ADMIN') : $admin;
    }

    /**
     * Builds the form.
     *
     * @param FormBuilder   $builder The form builder
     * @param array         $options The options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('user', new UserType($this->admin, $this->user));

        if ($this->user->getIcon()) {
            $builder->add('delete_icon', 'checkbox', array(
                'label' => 'Remove Icon',
                'required' => false
            ));
        }

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
        return 'pw_user_edit';
    }
}
