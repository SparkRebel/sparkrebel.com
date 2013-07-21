<?php

namespace PW\AssetBundle\Handler;

abstract class Common
{

    /**
     * rootpath under which all assets are uploaded
     */
    protected $path = '';

    public function __construct($params = array()) {
        foreach($params as $key => $value) {
            $this->$key = $value;
        }
    }

    public function move($base, $source, $target) {
        if ($this->copy($base, $souce, $target)) {
            unlink($source);
            return true;
        }
        return false;
    }

}
