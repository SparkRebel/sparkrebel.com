<?php

namespace PW\AssetBundle\Handler;

class File extends Common
{
    /**
     * rootpath under which all assets are uploaded
     */
    protected $path = '/assets/';

    public function copy($source, $target)
    {
        $target = parse_url($target, PHP_URL_PATH);
        $path = dirname(dirname(dirname(dirname(__DIR__)))) . '/web' . $this->path . $target;

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        //var_dump($source); var_dump($path);die();
        copy($source, $path);
        chmod($path, 0755);
        return $this->path . $target;
    }
}
