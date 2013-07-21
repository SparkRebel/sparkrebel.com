<?php

namespace PW\CategoryBundle\Command;

use PW\CategoryBundle\Document\Area,
    Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;

/**
 * SeedAreasCommand
 */
class AreaSeedCommand extends ContainerAwareCommand
{
    /**
     * document manager placeholder instance
     */
    protected $dm;

    protected $areas = array(
        'Boho Chic',
        'Romantic & Sweet',
        'Indie & Edgy',
        'Preppy',
        'Vintage Lover',
        'Sporty',
        'Trendy'
    );

    protected $areaRenames = array(
        'Jewlery Junkie' => 'Jewelry Junkie'
    );

    /**
     * repo
     */
    protected $repo;

    /**
     * Checks whether the command is enabled or not in the current environment
     *
     * Override this to check for x or y and return false if the command can not
     * run properly under the current conditions.
     *
     * @return Boolean
     */
    public function isEnabled()
    {
        return !($this->getContainer()->getParameter('kernel.environment') === 'prod');
    }

    /**
     * configure
     */
    protected function configure()
    {
        $this
            ->setName('area:seed')
            ->setDescription('Create the list of areas used in the registration process')
            ->setDefinition(array(
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

        $this->dm = $this->getContainer()
            ->get('doctrine_mongodb.odm.document_manager');
        $this->repo = $this->dm->getRepository('PWCategoryBundle:Area');

        foreach ($this->areas as $area) {
            $output->write("\t$area\n");
            $this->find($area);
        }
    }

    /**
     * find
     *
     * @param string $name area to find
     *
     * @return area instance
     */
    protected function find($name)
    {
        $area = $this->repo->findOneBy(
            array(
                'name' => $name,
            )
        );

        if (!$area) {
            $area = new Area();
            $area->setName($name);
        } else {
            if (!empty($this->areaRenames[$name])) {
                $area->setName($this->areaRenames[$name]);
            }
        }

        $this->dm->persist($area);
        $this->dm->flush();

        return $area;
    }
}
