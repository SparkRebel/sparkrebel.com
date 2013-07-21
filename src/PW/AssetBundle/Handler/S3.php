<?php

namespace PW\AssetBundle\Handler;

class S3 extends Common
{
    protected $accessKey = '';
    protected $secretKey = '';

    public function copy($bucket, $source, $target) {
        $s3 = new S3($this->accessKey, $this->secretKey);

        $input = S3::inputFile($source);
        $metaHeaders = array();
        $requestHeaders = array(
            'Cache-Control' => "max-age=315360000",
            'Expires' => gmdate("D, d M Y H:i:s T", strtotime("+10 years"))
        );

	    if (!$s3->putObject($input, $bucket, $target, S3::ACL_PUBLIC_READ, $metaHeaders, $requestHeaders)) {
            throw new \Exception("Failed to upload to S3");
        }

        return "http://{$bucket}/{$target}";
    }
}
