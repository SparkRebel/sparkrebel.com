<?php

namespace PW\AssetBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\ArrayInput,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\NullOutput,
    Symfony\Component\Console\Output\OutputInterface;

/**
 * Create local versions of an asset
 */
class AssetSyncCommand extends AssetVersionCommand
{

    protected $s3Bucket = null;
    
    const JPEG_QUALITY = 80; 

    /**
     * configure
     */
    protected function configure()
    {
        $this
            ->setName('asset:sync')
            ->setDescription('Reprocess an asset and sync versions on s3')
            ->setDefinition(array(
                new InputArgument(
                    'assetId',
                    InputArgument::REQUIRED,
                    'The asset id or hash'
                ),
                new InputOption('verbose', '-v', InputOption::VALUE_NONE, 'Show output of all commands')
            ));
    }

    /**
     * Reprocess an asset
     *
     * If it's not local - download it first.
     * Reprocess the asset, then upload it to the s3
     *
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->imagine = $this->getContainer()->get('imagine');
        $this->filterManager = $this->getContainer()->get('imagine.filter.manager');
        $this->s3Bucket = $this->getContainer()->getParameter('s3_bucket_default');

        $assetId = $input->getArgument('assetId');
        $verbose = $input->getOption('verbose');

        $this->dm = $this->getContainer()
            ->get('doctrine_mongodb.odm.document_manager');

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

        if ($verbose) {
            $subOutput = $output;
        } else {
            $subOutput = new NullOutput();
        }

        $output->write("Retrieving original file\n");
        list($id, $inputFile) = $this->getOriginal($asset, $subOutput);
        
        //$output->write("inputFile: ".$inputFile."\n");
        $canUpload = false;
        if (strpos($inputFile, 'web/assets/')!==false || strpos($inputFile, 'web/images/banners/')!==false) {
            // if image is in web/assets dir or web/images/banners dir -> we can move it to s3
            // from other dirs better not
            $canUpload = true;
        }
        $output->write("canUpload: ".intval($canUpload)."\n");

        $output->write("Deleting local versions\n");
        $this->deleteLocalVersions($inputFile, false, $subOutput);

        $output->write("Regenerating versions locally\n");
        $this->callVersionCommand($id, $subOutput);
        
        // upload to S3
        $uploaded = false;
        $this->uploaded_files = array();
        if ($canUpload && $this->s3Bucket) {
            $output->write("Uploading files to s3\n");
            $uploaded = $this->uploadFilesToS3($asset, $inputFile, $subOutput);
        }
         
        $output->write("Updating asset db data\n");
        
        $asset->setThumbsExtension('jpg');
   
        $thumbs_extension = strtolower( substr($asset->getUrl(),-3) );
        if ($thumbs_extension=='png' && $asset->getAllowPng()) {
            $asset->setThumbsExtension('png');
        } else {
            $asset->setThumbsExtension('jpg');
        }
        $asset->setDimensions($inputFile);
        $asset->setIsActive(true); // activate asset
        $this->dm->persist($asset);
        $this->dm->flush();

        if ($canUpload && $uploaded) {
            $output->write("Removing any extra versions on the s3\n");
            $this->cleanS3($inputFile, $subOutput);

            $output->write("Deleting all local files\n");
            $this->deleteLocalVersions($inputFile, true, $subOutput);
        }

        $output->write("asset:sync $assetId finished\n");
    }

    /**
     * getOriginal
     *
     * The returned id is used in the commands called. It is either the id of the asset (if the
     * asset is local) or the abs file path if the asset is already on the s3 (because otherwise
     * calling the version command will fail
     *
     * @param mixed $asset
     *
     * @return array(id, filepath)
     */
    protected function getOriginal($asset, $output)
    {
        $url = $asset->getUrl();

        if ($url[0] === '/') {
            $inputFile = dirname(dirname(dirname(dirname(__DIR__)))) . '/web' . $url;
            if (!file_exists($inputFile)) {
                // try to get it from our production server
                if (@copy("http://sparkrebel.com" . $url, $inputFile)) {
                    $output->write("success - got file from our prod (sparkrebel.com)\n");
                }
            }
            $return = array($asset->getId(), $inputFile);
        } else {
            $inputFile = dirname(dirname(dirname(dirname(__DIR__)))) . '/web/assets/' . basename($url);
            $output->write("Attempting to download $url...");
            if (@copy($url, $inputFile)) {
                $output->write("success\n");
            } else {
                $output->write("fail\n");
            }
            $return = array($inputFile, $inputFile);
        }

        if (!file_exists($inputFile)) {
            $sourceUrl = $asset->getSourceUrl();
            if (!$sourceUrl) {
                $source = $asset->getSource();
                throw new \Exception("$url does not exist and no sourceUrl is available (type $source) - cannot continue");
            }
            $output->write("Attempting to download $sourceUrl...");
            if (@copy($sourceUrl, $inputFile)) {
                if ($this->getContainer()->get('pw.asset')->isPngFile($inputFile)) {
                    $output->write("converting png...\n");
                    $imagine = new \Imagine\Imagick\Imagine();
                    $image = $imagine->open($inputFile);  
                    // we convert png image to non png image, so we need to make transparent to white color
                    $background = new \Imagine\Image\Color('#fff');
                    $topLeft    = new \Imagine\Image\Point(0, 0);
                    $canvas     = $imagine->create($image->getSize(), $background);

                    $canvas
                        ->paste($image, $topLeft)
                        ->save($inputFile, array('quality' => self::JPEG_QUALITY))
                    ;
                }
                $output->write("success\n");
            } else {
                $output->write("fail\n");
                throw new \Exception("Could not download $sourceUrl - cannot continue");
            }
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
    protected function deleteLocalVersions($inputFile, $allOfThem, $output)
    {
        $pattern = preg_replace('@\.[^\.]+$@', '.*', $inputFile);
        $files = glob($pattern);
        foreach ($files as $file) {
            if (!$allOfThem && $file === $inputFile) {
                $output->write("Keeping $file\n");
                continue;
            }
            $output->write("Deleting $file\n");
            unlink($file);
        }
    }

    /**
     * callVersionCommand
     *
     * @param mixed $id
     * @param mixed $output
     */
    protected function callVersionCommand($id, $output)
    {
        $command = $this->getApplication()->find('asset:version');
        $arguments = array(
            'command' => 'asset:version',
            'assetId' => $id
        );
        $input = new ArrayInput($arguments);
        $returnCode = $command->run($input, $output);
    }

    /**
     * uploadFilesToS3
     *
     * @param mixed $asset
     * @param mixed $inputFile
     * @param mixed $output
     *
     * @return files were uploaded
     */
    protected function uploadFilesToS3($asset, $inputFile, $output)
    {
        $root = dirname(dirname(dirname(dirname(__DIR__)))) . '/web';
        $original = substr($inputFile, strlen($root)) ;
        $bucket = $this->s3Bucket;

        if (!$bucket) {
            $asset->setUrl($original);
            return;
        }

        $pattern = preg_replace('@\.[^\.]+$@', '.*', $inputFile);
        $files = glob($pattern);
        
        $this->uploaded_files = array();
        foreach ($files as $file) {
            $path = substr($file, strlen($root) + 1);
            $url = "http://$bucket/$path";
            if (!$this->s3->putObjectFile($file, $bucket, $path, \S3::ACL_PUBLIC_READ)) {
                throw new \Exception("Failed to upload to S3");
            }
            $this->uploaded_files[] = $path;
            $output->write("$url uploaded\n");
        }

        $asset->setUrl('http://' . $bucket . $original);
        return true;
    }

    /**
     * cleanS3
     *
     * If we change our image names/sizes we are quite likely to end up with files on the s3 that
     * we aren't going to use - this will delete any files that we didn't just upload
     *
     * @param mixed $inputFile
     * @param mixed $output
     */
    protected function cleanS3($inputFile, $output)
    {
        $bucket = $this->s3Bucket;
        if (!$bucket) {
            return;
        }

        $root = dirname(dirname(dirname(dirname(__DIR__)))) . '/web';
        //$pattern = preg_replace('@\.[^\.]+$@', '.*', $inputFile);
        //$localFiles = glob($pattern);

        $prefix = 'assets/' . substr(basename($inputFile), 0, -4);

        $s3Files = array_values($this->s3->getBucket($bucket, $prefix));
        foreach ($s3Files as &$file) {
            $file = $file['name'];
            $output->write("$file found on S3\n");
        }

        $filesToKeep = $this->uploaded_files;
        foreach ($this->uploaded_files as $uploaded_file) {
            if (substr($uploaded_file, -5, 1)=='l') {
                // keep old png thumbs for facebook
                $filesToKeep[] = substr($uploaded_file, 0, -3).'png';
                $output->write("keep old (png?) thumb for facebook on S3: ".substr($uploaded_file, 0, -3).'png'."\n");
            }
        }
        $filesToKeep = array_unique($filesToKeep);
        $extraFiles = array_diff($s3Files, $filesToKeep);

        foreach ($extraFiles as $file) {
            $this->s3->deleteObject($bucket, $file);
            $output->write("$file deleted from S3\n");
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
        if ($field === 's3') {
            $accessKey = $this->getContainer()->getParameter('s3_access_key');
            $secretKey = $this->getContainer()->getParameter('s3_secret_key');
            return $this->s3 = new \S3($accessKey, $secretKey);
        }
    }

}
