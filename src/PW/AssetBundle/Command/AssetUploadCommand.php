<?php

namespace PW\AssetBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;

/**
 * Upload all versions of an asset to the s3
 */
class AssetUploadCommand extends ContainerAwareCommand
{

    /**
     * document manager placeholder
     */
    protected $dm;

    /**
     * configure
     */
    protected function configure()
    {
        $this
            ->setName('asset:upload')
            ->setDescription('Move all versions for an asset to the s3')
            ->setDefinition(array(
                new InputArgument(
                    'assetId',
                    InputArgument::REQUIRED,
                    'The asset id or hash'
                )
            ));
    }

    /**
     * Find all versions for the asset, upload them and change the stored path of the asset
     *
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $bucket = $this->getContainer()->getParameter('s3_bucket_default');
        if (!$bucket) {
            throw new \Exception("No default s3 bucket defined - nothing to do");
        }

        $assetId = $input->getArgument('assetId');

        $this->dm = $this->getContainer()->get('doctrine_mongodb.odm.document_manager');
        $assetRepo = $this->dm->getRepository('PWAssetBundle:Asset');

        if (strlen($assetId) === 40) {
            $asset = $assetRepo->findOneByHash($assetId);
            if ($asset) {
                $assetId = $asset->getId();
            }
        } else {
            $asset = $assetRepo->find(new \MongoId($assetId));
        }
        if (!$asset) {
            throw new \Exception("Asset $assetId doesn't exist");
        }

        $mainFile = $asset->getUrl();

        if ($mainFile[0] !== '/') {
            throw new \Exception("$mainFile is not a local path - this command can only process local files");
        }
        $root = dirname(dirname(dirname(dirname(__DIR__)))) . '/web';
        $inputFile = $root . $mainFile;

        if (!file_exists($inputFile)) {
            throw new \Exception("$inputFile not found - this command can only process local files");
        }

        $pattern = preg_replace('@\.[^\.]+$@', '.*', $inputFile);
        $files = glob($pattern);

        foreach ($files as $file) {
            $path = substr($file, strlen($root) + 1);
            $url = $this->uploadFile($file, $path, $bucket);
            $output->write("$url uploaded\n");
        }

        $asset->setUrl('http://' . $bucket . $mainFile);
        $this->dm->persist($asset);
        $this->dm->flush();
        
        $this->deleteLocalVersions($inputFile, false, $output);
        
        $output->write("Asset $assetId ($mainFile) processed (uploaded and local versions deleted)\n");

        $this->event->publish(
            'asset.uploaded',
            array(
                'assetId' => $assetId
            )
        );
    }

    /**
     * upload a single file
     *
     * @param string $source where is the file to be uploaded
     * @param string $target where to upload to
     * @param string $bucket which bucket to add to
     *
     * @return the location of the uploaded file
     */
    protected function uploadFile($source, $target = '', $bucket = '')
    {
        if (!$target) {
            $target = ltrim($source, '/');
        }
        if (!$bucket) {
            $bucket = $this->getContainer()->getParameter('s3_bucket_default');
        }

        $input = \S3::inputFile($source);
        $metaHeaders = array();
        $requestHeaders = array(
            'Cache-Control' => "max-age=315360000",
            'Expires' => gmdate("D, d M Y H:i:s T", strtotime("+10 years"))
        );

        $return = "http://$bucket/$target";

	    if (!$this->s3->putObject($input, $bucket, $target, \S3::ACL_PUBLIC_READ, $metaHeaders, $requestHeaders)) {
            throw new \Exception("Failed to upload to S3");
        }

        return $return;
    }
    
    /**
     * Delete local files
     *
     * Since we just glob to find files - make sure there any any extra file floating around.
     * Unless all of them is set to true - in which case wipe them all out
     *
     * @param mixed $inputFile
     * @param mixed $allOfThem
     */    
    protected function deleteLocalVersions($inputFile, $allOfThem = false, $output)
    {
        $pattern = preg_replace('@\.[^\.]+$@', '.*', $inputFile);
        $files = glob($pattern);
        foreach ($files as $file) {
            if (!$allOfThem && $file === $inputFile) {
                $output->write("<info>Keeping $file</info>\n");
                continue;
            }
            $output->write("<info>Deleting $file</info>\n");
            unlink($file);
        }
    }
    
    /**
     * Lazy load/reference any dependent services
     *
     * @param string $field the field of this class
     *
     * @return mixed
     */
    public function __get($field)
    {
        if ($field === 'event') {
            return $this->event = $this->getContainer()->get('pw.event');
        }
        if ($field === 's3') {
            $accessKey = $this->getContainer()->getParameter('s3_access_key');
            $secretKey = $this->getContainer()->getParameter('s3_secret_key');
            return $this->s3 = new \S3($accessKey, $secretKey);
        }
    }
}
