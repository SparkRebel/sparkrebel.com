<?php

namespace PW\AssetBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;
use PW\AssetBundle\Extension\AssetUrl;

/**
 * Create local versions of an asset
 */
class AssetVersionCommand extends ContainerAwareCommand
{
    const JPEG_QUALITY = 80; 

    /**
     * document manager placeholder
     */
    protected $dm;

    /**
     * filterManager placeholder
     */
    protected $filterManager;

    /**
     * imagine lib placeholder
     */
    protected $imagine;

    protected $verbose = false;

    /**
     * configure
     */
    protected function configure()
    {
        $this
            ->setName('asset:version')
            ->setDescription('Create versions of each file in the right place')
            ->setDefinition(array(
                new InputArgument('assetId', InputArgument::REQUIRED, 'The asset id or hash'),
                //new InputOption('customFile', '-f', InputOption::VALUE_OPTIONAL, 'Custom local file to process (instead of orgiginal $asset->getUrl())', false)
            ));
    }

    /**
     * Generate each version for an image
     *
     * Generate them in descending size order and use the output of each filter as the input
     * for the next. So generate large, use large to generate medium, use medium to generate
     * small, use small to generate thumb.
     *
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->imagine = $this->getContainer()->get('imagine');
        $this->filterManager = $this->getContainer()->get('imagine.filter.manager');

        $assetId = $input->getArgument('assetId');
        $emitEvent = false;
        
        $thumbs_extension = 'jpg';
        
        if (file_exists($assetId)) {
            $inputFile = $assetId;
            $this->verbose = true;
            $pos = strrpos($inputFile, 'web');
            $urlInWeb = substr($inputFile, $pos+3);
        } else {
            $emitEvent = true;
            $this->dm = $this->getContainer()
                ->get('doctrine_mongodb.odm.document_manager');

            $assetRepo = $this->dm->getRepository('PWAssetBundle:Asset');

            if (strlen($assetId) === 40) {
                $asset = $assetRepo->findOneByHash($assetId);
                if ($asset) {
                    $assetId = $asset->getId();
                }
            } else {
                $asset = $assetRepo->find($assetId);
            }
            if (!$asset) {
                throw new \Exception("Asset $assetId doesn't exist");
            }

            $file = $asset->getUrl();

            if ($file[0] !== '/') {
                throw new \Exception("$file is not a local path - this command can only process local files");
            }
            $inputFile = dirname(dirname(dirname(dirname(__DIR__)))) . '/web' . $file;
            if (!file_exists($inputFile)) {
                throw new \Exception("$inputFile not found - this command can only process local files");
            }
            $urlInWeb = $file;
            
            if ($asset->getAllowPng()) {
                $pathInfo = pathinfo($inputFile);
                if (strtolower($pathInfo['extension'])=='png') {
                    $thumbs_extension = 'png';
                }
            }
        }
   
        /*$assetUrlExtension = new AssetUrl();
        if ( $assetUrlExtension->hasLocalVersionUrl($urlInWeb, 'small') ) {
            $output->writeLn("Asset $assetId already has versions -> exiting\n");
            return true;
        }*/

        $image = $this->imagine->open($inputFile);
        $size = $image->getSize();
        $width = $size->getWidth();
        $height = $size->getHeight();
        $ratio = round($width / $height, 3);

        $versions = $this->getContainer()->getParameter('imagine.filters');
        $this->processAsset($inputFile, compact('width', 'height'), $versions, $output, $thumbs_extension);

        /*if (!empty($asset)) {
            $asset->setDimensions($image);
            $asset->setThumbsExtension($thumbs_extension);
            $asset->setIsActive(true);
            $this->dm->persist($asset);
            $this->dm->flush();
        }*/

        /* if ($emitEvent) {
            $bucket = $this->getContainer()->getParameter('s3_bucket_default');
            if ($bucket) {
                $event = $this->getContainer()->get('pw.event');
                $event->publish(
                    'asset.processed',
                    array(
                        'assetId' => $assetId
                    )
                );
            }
        } */
        
        $output->write("asset:version $assetId finished\n");
    }

    /**
     * processAsset
     *
     * Process an asset generating all versions specified
     *
     * @param string $inputFile  where is the input file
     * @param array  $dimensions width and height of original image
     * @param array  $versions   array of version information
     * @param object $output     interface
     */
    protected function processAsset($inputFile, $dimensions, $versions, $output, $thumbs_extension)
    {
        $originalFile = preg_replace('@\.[^\.]*$@', '.'.$thumbs_extension , $inputFile);
        foreach (array_reverse($versions) as $version => $params) {
            $outputFile = preg_replace('@\.([^\.]*)$@', '.' . $version[0] . '.\1', $originalFile);
            if ($dimensions['width'] < $params['options']['size'][0] && $dimensions['height'] < $params['options']['size'][1]) {
                $this->imagine->open($inputFile)->save($outputFile, array('quality' => self::JPEG_QUALITY));
                $output->write("Original too small, copied input file to $outputFile\n");
                continue;
            }
            if ($this->processAssetVersion($inputFile, $outputFile, $version, $output)) {
                $output->write("Generated $outputFile\n");
            }
        }

    }

    /**
     * processAssetVersion
     *
     * Process an asset generating one specific version
     *
     * @param string $inputFile  where is the input file
     * @param string $outputFile where to store the result
     * @param string $filter     which filter to apply
     * @param object $output     interface
     *
     * @return result of applied filter
     */
    protected function processAssetVersion($inputFile, $outputFile, $filter, $output)
    {
        $return = $this->filterManager->get($filter)
            ->apply($this->imagine->open($inputFile))
            ->save($outputFile, array('quality' => self::JPEG_QUALITY));

        if ($this->verbose) {
            $size = getimagesize($outputFile);
            $output->write($size[3] . ' ');
        }
        return $return;
    }

}
