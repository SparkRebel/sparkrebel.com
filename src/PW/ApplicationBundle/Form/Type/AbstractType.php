<?php

namespace PW\ApplicationBundle\Form\Type;

use Symfony\Component\Form\AbstractType as BaseType;
use Symfony\Component\Form\FormBuilderInterface;

abstract class AbstractType extends BaseType
{
    /**
     * @var \PW\UserBundle\Document\User
     */
    protected $_user;

    /**
     * Builds the form.
     *
     * @param FormBuilder   $builder The form builder
     * @param array         $options The options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $data = $options['data'];
        if (is_object($data) && method_exists($data, 'getCreatedBy')) {
            $this->_user = $data->getCreatedBy();
        } else {
            if (isset($data['user']) && $data['user'] instanceOf \PW\UserBundle\Document\User) {
                $this->_user = $data['user'];
            }
        }
    }

    /**
     * @return \PW\UserBundle\Document\User|null
     */
    public function getUser()
    {
        return $this->_user;
    }
}
