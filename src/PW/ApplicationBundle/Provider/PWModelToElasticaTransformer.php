<?php

namespace PW\ApplicationBundle\Provider;

use Elastica_Document;
use RuntimeException;

abstract class PWModelToElasticaTransformer extends \FOQ\ElasticaBundle\Transformer\ModelToElasticaAutoTransformer
{
    abstract protected function normalizeConfig();

    /**
     * Transforms an object into an elastica object having the required keys
     *
     * @param object $object the object to convert
     * @param array $fields the keys we want to have in the returned array
     * @return Elastica_Document
     **/
    public function transform($object, array $fields)
    {
        $normalizeConfig = $this->normalizeConfig();

        $class = get_class($object);
        $array = array();
        foreach ($fields as $key => $fieldOptions) {
            if (isset($normalizeConfig[$key])) {
                $conf = $normalizeConfig[$key];
            } else {
                $conf = array('type' => 'standard');
            }

            if($conf['type'] == 'synthetic') {
                $array[$key] = $conf['fn']($object);
            } else {
                $getter = 'get'.ucfirst($key);
                if (!method_exists($class, $getter)) {
                    throw new \Exception(sprintf('The getter %s::%s does not exist', $class, $getter));
                }

                $value = $object->$getter();

                if ($conf['type'] == 'standard') {
                    $array[$key] = $this->normalizeValue($value);
                } else {
                    $array[$key] = $this->{$conf['type']}($value, $conf);
                }
            }
        }

        $identifierGetter = 'get'.ucfirst($this->options['identifier']);
        $identifier = $object->$identifierGetter();

        return new Elastica_Document($identifier, array_filter($array));
    }

    protected function arrayNormalize($value, $conf) {
        $value = iterator_to_array($value);

        array_walk_recursive($value, function(&$v) use($conf) {
            $v = $v->{$conf['valueFn']}();
        });

        return $value;
    }

    protected function useId($value) {
        if (empty($value)) {
            return null;
        }
        return $value->getId();
    }
}
