<?php

namespace PW\FeatureBundle\Command;

use PW\FeatureBundle\Document\Feature,
    PW\UserBundle\Document\User,
    Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;

/**
 * Create a feature record for an active brand
 */
class FeatureBrandCommand extends ContainerAwareCommand
{

    /**
     * document manager placeholder instance
     */
    protected $dm;

    /**
     * configure
     */
    protected function configure()
    {
        $this
            ->setName('feature:brand')
            ->setDescription('Feature a brand')
            ->setDefinition(array(
                new InputArgument(
                    'brand',
                    InputArgument::REQUIRED,
                    'The brand id or name or username'
                )
            ));
    }

    /**
     * execute
     *
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $brandId = $input->getArgument('brand');

        $this->dm = $this->getContainer()
            ->get('doctrine_mongodb.odm.document_manager');
        $this->featureRepo = $this->dm->getRepository('PWFeatureBundle:Feature');
        $this->brandRepo = $this->dm->getRepository('PWUserBundle:Brand');

        $brand = $this->brandRepo->find($brandId);
        if (!$brand) {
            $brand = $this->brandRepo->findOneBy(
                array(
                    '$or'  => array(
                        array('username' => $brandId),
                        array('name' => $brandId),
                    )
                )
            );
            if (!$brand) {
                throw new \Exception("Brand $brandId doesn't exist");
            }
            $brandId = $brand->getId();
        }

        $conditions = array(
            'target.$id' => new \MongoId($brandId),
            'isActive' => true
        );
        $feature = $this->featureRepo->findOneBy($conditions);
        if ($feature) {
            $action = 'updated';
        } else {
            $action = 'created';
            $feature = new Feature();
            $feature->setIsActive(true);
            $feature->setTarget($brand);
        }

        $this->dm->persist($feature);
        $this->dm->flush();

        $output->write("$action feature record for borad $brandId\n");
    }

}
