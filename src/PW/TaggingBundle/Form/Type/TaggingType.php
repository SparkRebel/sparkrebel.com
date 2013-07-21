<?php

namespace PW\TaggingBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class TaggingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')            
        ;
    }

    public function getName()
    {
        return 'tagging';
    }

    public function getDefaultOptions(array $options) {
        return array(
            'data_class' => 'PW\TaggingBundle\Document\Tagging'
        );
    }
}
