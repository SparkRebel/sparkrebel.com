<?php

namespace PW\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use PW\UserBundle\Form\Type\RegistrationFormType;

class PartnerRegistrationFormType extends RegistrationFormType
{
    /**
     * @var \PW\UserBundle\Document\User
     */
    protected $_user;

    public function __construct()
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        // Check for a User
        $data = $options['data'];
        if (is_object($data) && method_exists($data, 'getUser')) {
            $this->_user = $data->getUser();
        } else {
            if (isset($data['user']) && $data['user'] instanceOf \PW\UserBundle\Document\User) {
                $this->_user = $data['user'];
            }
        }

        if ($this->getUser()) {
            $builder->remove('plainPassword');
        }

        $builder->add('phone', 'text', array(
            'label'    => 'Phone',
            'required' => true,
        ));

        $builder->add('link', 'url', array(
            'label'    => 'Site URL',
            'required' => true,
        ));

        $builder->add('requestedSlug', 'text', array(
            'label'    => 'Requested URL',
            'required' => true,
        ));

        $builder->add('icon', 'file', array(
            'label'         => 'Icon',
            'required'      => false,
            'property_path' => false,
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            array('data_class' => 'PW\UserBundle\Document\Partner')
        ));
    }

    public function getName()
    {
        return 'user_partner_registration';
    }

    /**
     * @return \PW\UserBundle\Document\User|null
     */
    public function getUser()
    {
        return $this->_user;
    }
}
