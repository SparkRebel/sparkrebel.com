<?php

namespace PW\AssetBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\ArrayInput,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\NullOutput,
    Symfony\Component\Console\Output\OutputInterface;

/**
 * Create local versions of an asset
 */
class AssetRemoveCommand extends AssetSyncCommand
{
    protected $placeholder;

    /**
     * configure
     */
    protected function configure()
    {
        $this
            ->setName('asset:remove')
            ->setDescription('Remove an asset and replace references to it with a placeholder')
            ->setDefinition(array(
                new InputArgument(
                    'assetId',
                    InputArgument::REQUIRED,
                    'The asset id or hash'
                )
            ));
    }

    /**
     * Remove an asset
     *
     * This would be after a takedown request or flagged as inappropriate
     *
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // $this->s3Bucket = $this->getContainer()->getParameter('s3_bucket_default');

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

        // $output->write("Deleting local versions\n");
        // $this->deleteLocalVersions($inputFile, false, $subOutput);
        //
        // $output->write("Removing any extra versions on the s3\n");
        // $this->cleanS3($inputFile, $subOutput);

        $asset->setDeleted(time());
        $this->dm->persist($asset);
        $this->dm->flush();

        $output->write("Asset $assetId deleted\n");
    }
}
