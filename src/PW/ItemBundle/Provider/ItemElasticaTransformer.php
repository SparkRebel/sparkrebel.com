<?php

namespace PW\ItemBundle\Provider;

class ItemElasticaTransformer extends \PW\ApplicationBundle\Provider\PWModelToElasticaTransformer
{
    protected function normalizeConfig()
    {
        return array(
            'isActive' => array(
                'type' => 'synthetic',
                'fn' => function($obj){
                    $rootPost = $obj->getRootPost();
                    
                    return ($obj->getIsActive() && $rootPost && $rootPost->getIsActive());
                },
            ),
            
            'categories' => array(
                'type' => 'arrayNormalize',
                'valueFn' => 'getId',
            ),

            'merchantUser' => array(
                'type' => 'useId',
            ),

            'brandUser' => array(
                'type' => 'useId',
            ),
            
            'bmcat' => array(
                'type' => 'synthetic',
                'fn' => function($object) {
                    $arr = array();
                    
                    foreach(array('merchantUser', 'brandUser') as $k) {
                        $u = $object->{'get' . $k}();
                        if ($u) {
                            $arr[] = $u->getId();
                        }
                    }
                    
                    return array_unique($arr);
                },
            ),
            
            'bmNames' => array(
                'type' => 'synthetic',
                'fn' => function($object) {
                    $arr = array();
                    
                    foreach(array('merchantName', 'brandName') as $k) {
                        $u = $object->{'get' . $k}();
                        if ($u) {
                            $arr[] = $u;
                        }
                    }
                    
                    return array_unique($arr);
                },
            ),
        );
    }
}
