<?php

namespace PW\NewsletterBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class NewsletterType extends AbstractType
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
            throw new \Exception('User required to build form');
        }

        $user = $this->user;

        $builder->add('subject', 'text', array(
            'required' => false,
            'label'    => 'Subject',
        ));
        
        $builder->add('heading', 'text', array(
            'required' => false,
            'label'    => 'Heading (text under: Hi [yourname]!)'
        ));

        $builder->add('topType', 'choice', array(
            'label'   => 'Top Section',
            'multiple' => false,
            'expanded' => true,
            'choices' => array(
                'n/a' => 'N/A',
                "curated" => 'Curated',
                "brands" => 'Brands',
                "celebs" => 'Celebs',
                "events" => 'Events'
            )
        ));

        $builder->add('curatedTopTitle', 'text', array(
            'required' => false,
            'label'    => 'Title',
        ));

        $builder->add('curatedTopImage', 'file', array(
            'required' => false,
            'label'    => 'Photo',
            'property_path' => false,
        ));

        $builder->add('curatedTopLink', 'text', array(
            'required' => false,
            'label'    => 'Photo Link',
        ));

        $builder->add('curatedTopDescription', 'textarea', array(
            'required' => false,
            'label'    => 'Photo Description',
        ));

        $builder->add('curatedTopContent', 'textarea', array(
            'required' => false,
            'label'    => 'Content',
            'attr'     => array(
                'class' => 'tinymce',
                'data-theme' => 'advanced',
            )
        ));

        $builder->add('eventsTopBoard', 'document', array(
                    'class' => 'PWBoardBundle:Board',
                    'property'          => 'name',
                    'label'             => 'Events Top Collection',
                    'empty_value'       => '-- Select Event --',
                    'required' => false,
                    'query_builder' => function($repository) use($user) {

                        return $repository->createQueryBuilder('b')
                            ->field('images')->prime()
                            ->field('createdBy')->references($user)
                            ->field('isActive')->equals(true)
                            ->sort('created', 'asc');
                        }
        ));

        $builder->add('bottomType', 'choice', array(
            'label'   => 'Bottom Section',
            'multiple' => false,
            'expanded' => true,
            'choices' => array(
                'n/a' => 'N/A',
                "curated" => 'Curated',
                "brands" => 'Brands',
                "celebs" => 'Celebs',
                "events" => 'Events'
            )
        ));

        $builder->add('curatedBottomTitle', 'text', array(
            'required' => false,
            'label'    => 'Title',
        ));

        $builder->add('curatedBottomImage', 'file', array(
            'required' => false,
            'label'    => 'Photo',
            'property_path' => false,
        ));

        $builder->add('curatedBottomLink', 'text', array(
            'required' => false,
            'label'    => 'Photo Link',
        ));

        $builder->add('curatedBottomDescription', 'textarea', array(
            'required' => false,
            'label'    => 'Photo Description',
        ));

        $builder->add('curatedBottomContent', 'textarea', array(
            'required' => false,
            'label'    => 'Content',
            'attr'     => array(
                'class' => 'tinymce',
                'data-theme' => 'advanced',
                )
        ));

        $builder->add('eventsBottomBoard', 'document', array(
            'class' => 'PWBoardBundle:Board',
            'property'          => 'name',
            'label'             => 'Events Bottom Collection',
            'empty_value'       => '-- Select Event --',
            'required' => false,
            'query_builder' => function($repository) use($user) {

                return $repository->createQueryBuilder('b')
                    ->field('images')->prime()
                    ->field('createdBy')->references($user)
                    ->field('isActive')->equals(true)
                    ->sort('created', 'asc');
            }
        ));

        $builder->add('status', 'choice', array(
            'label'   => 'Status',
            'choices' => array(
                "review" => "Review",
                "pending" => "Pending",
                "sent" => "Sent"
            )
        ));

        $builder->add('sendAt', 'datetime', array(
            'label'   => 'Send At'
        ));
        
        $builder->add('showTrendingCollections', 'choice', array(
            'label'   => 'Show "TrendingCollections" section',
            'choices' => array(
                '1' => 'Yes',
                '0' => 'No'
            )
        ));
        
        $builder->add('showFromYourStream', 'choice', array(
            'label'   => 'Show "FromYourStream" section',
            'choices' => array(
                '1' => 'Yes',
                '0' => 'No'
            )
        ));
        
        $builder->add('showFromYourFavoriteBrands', 'choice', array(
            'label'   => 'Show "FromYourFavoriteBrands" section',
            'choices' => array(
                '0' => 'No',
                '1' => 'Yes'
            )
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'PW\NewsletterBundle\Document\Newsletter',
        ));
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'pw_newsletter';
    }
}
