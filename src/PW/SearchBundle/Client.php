<?php

namespace PW\SearchBundle;

use FOQ\ElasticaBundle\Client as BaseClient;

class Client extends BaseClient
{
    /**
     * Because I don't want to run ElasticSearch in dev
     *
     * @param type $path
     * @param type $method
     * @param type $data
     * @return \Elastica_Response
     */
    public function request($path, $method, $data = array(), array $query = array())
    {
        try {
            return parent::request($path, $method, $data, $query);
        } catch (\Elastica_Exception_Abstract $e) {
            return new \Elastica_Response('{"took":0,"timed_out":false,"hits":{"total":0,"max_score":0,"hits":[]}}');
        }
    }
}