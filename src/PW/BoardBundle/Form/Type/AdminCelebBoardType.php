<?php

namespace PW\BoardBundle\Form\Type;

use Symfony\Component\Form\AbstractType,
    Symfony\Component\Form\FormBuilderInterface,
    PW\CategoryBundle\Repository\CategoryRepository;

class AdminCelebBoardType extends AbstractType
{
    /**
     * Builds the form.
     *
     * @param FormBuilder   $builder The form builder
     * @param array         $options The options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
       	$builder->add('name', 'text', array(
            'required'  => true,
            'label'     => 'Collection Name',
        ));
    }

	public function getDefaultOptions(array $opts)
	{
	    return array(
	         'data_class' => 'PW\BoardBundle\Document\Board',
	    );
	}
    

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'pw_admin_celeb_board';
    }


}
