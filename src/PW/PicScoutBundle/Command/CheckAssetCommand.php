<?php

namespace PW\PicscoutBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;

/**
 */
class CheckAssetCommand extends ContainerAwareCommand
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
            ->setName('pw:picscout:check')
            ->setDescription('Checks the asset against picscout api')
            ->setDefinition(array(
                new InputArgument(
                    'assetId',
                    InputArgument::REQUIRED,
                    'The asset id'
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

        $assetId = $input->getArgument('assetId');

        $this->dm = $this->getContainer()->get('doctrine_mongodb.odm.document_manager');
        $assetRepo = $this->dm->getRepository('PWAssetBundle:Asset');

        $asset = $assetRepo->find(new \MongoId($assetId));

        if (!$asset) {
            throw new \Exception("Asset $assetId doesn't exist");
        }

        $url = $asset->getUrl();

        $result  = $this->getContainer()->get('pw_picscout.service')->check($asset);
        if($result === true) {
        	$output->writeln("Asset {$assetId} can be used");
        } else {
        	$output->writeln("Asset {$assetId} cannot be used. Deleting");
            $this->getContainer()->get('pw_picscout.service')->deleteAsset($asset);
        	//todo delete and notify user
        }

    }


}
