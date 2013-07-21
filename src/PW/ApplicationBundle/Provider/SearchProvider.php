<?php

namespace PW\ApplicationBundle\Provider;

use Symfony\Component\DependencyInjection\ContainerAware;

/**
 * SearchProvider
 */
class SearchProvider extends ContainerAware
{
    public function makeSimpleOrFilter($field, $values)
    {
        $cnt = count($values);
        
        if (!$cnt) {
            return false;
        } else if ($cnt == 1) {
            return array('term' => array($field => $values[0]));
        } else {
            $out = array();
            foreach($values as $val) {
                $out[] = array('term' => array($field => $val));
            }
            
            return array('or' => $out);
        }
    }
    
    public function esRawSearch($type, array $esTerms) {
        $query = new \Elastica_Query($esTerms);
        
        $itemType = $this->container->get("foq_elastica.index.website.$type");
        return $itemType->search($query);
    }
    
    protected function hydrateResults($type, $repo, $results) {
        $ids = array_map(function($elasticaObject) use($type) {
            $id = $elasticaObject->getId();

            if (is_numeric($id) && $type == 'item') {
                return (int)$id;
            } else if (!preg_match('/^[\da-f]{24}$/', $id)) {
                //for non-mongo document ids
                return $id;
            } else {
                //assume mongo object ID
                return new \MongoId($id);
            }
        }, $results->getResults());

        $items = iterator_to_array($repo->findBy(array('_id' => array('$in' => $ids))));
        
        $sorted = array();
        foreach ($ids as $id) {
            $id = (string)$id;
            if (!empty($items[$id])) {
                $sorted[$id] = $items[$id];
            }
        }
        
        return $sorted;
    }
    
    public function esSearch($repo, $type, array $esTerms) {
        $results = $this->esRawSearch($type, $esTerms);
        
        return array(
            'total' => $results->getTotalHits(),
            'results' => $this->hydrateResults($type, $repo, $results),
        );
    }
}
