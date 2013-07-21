<?php

namespace PW\CategoryBundle\Command;

use PW\CategoryBundle\Document\Category,
    Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;

/**
 * CategoriesSeedCommand
 */
class CategoriesWeightCommand extends ContainerAwareCommand
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
            ->setName('category:weight')
            ->setDescription('Set category weight')
            ->setDefinition(array(
                new InputArgument(
                    'category',
                    InputArgument::REQUIRED,
                    'category name or id'
                ),
                new InputArgument(
                    'weight',
                    InputArgument::REQUIRED,
                    'weight to give'
                ),
                new InputArgument(
                    'type',
                    InputArgument::OPTIONAL,
                    'category type'
                ),
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
        $this->repo = $this->dm->getRepository('PWCategoryBundle:Category');

        $category = $input->getArgument('category');
        $weight   = (int)$input->getArgument('weight');
        $type     = $input->getArgument('type');

        if ($weight === 0 && $input->getArgument('weight') !== '0') {
            throw new \Exception("weight must be a number (" . $input->getArgument('weight') . ")");
        }

        $conditions = array(
            '$or' => array(
                array('id' => $category),
                array('id' => new \MongoId($category)),
                array('name' => $category),
            )
        );

        if (!empty($type)) {
            $conditions['type'] = $type;
        }

        if ($this->repo->findBy($conditions)->count() > 1) {
            throw new  \Exception("{$name} is ambigous");
        }

        $category = $this->repo->findOneBy($conditions);

        if (!$category) {
            throw new \Exception("Can't find category");
        }

        $category->setWeight($weight);
        $this->dm->persist($category);
        $this->dm->flush();
    }
}
