<?php

namespace PW\NewsletterBundle\Form\Type;

use Symfony\Component\Form\AbstractType,
    Symfony\Component\Form\FormBuilderInterface,
    PW\NewsletterBundle\Form\Type\NewsletterType;

class CreateNewsletterType extends AbstractType
{
    protected $user = NULL;

    public function setUser($user = NULL){
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
        if( ! $this->user ) {
            throw new \Exception('User required to build create form');
        }

        $type = new NewsletterType();
        $type->setUser($this->user);
        $builder->add('newsletter', $type);
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'pw_newsletter_create';
    }
}
