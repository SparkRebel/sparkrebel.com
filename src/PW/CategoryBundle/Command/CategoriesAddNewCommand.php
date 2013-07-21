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
class CategoriesAddNewCommand extends ContainerAwareCommand
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
            ->setName('category:add')
            ->setDescription('Add new category')
            ->setDefinition(array(
                new InputArgument(
                    'name',
                    InputArgument::REQUIRED,
                    'category name'
                ),
                new InputArgument(
                    'type',
                    InputArgument::REQUIRED,
                    'category type'
                ),
                new InputArgument(
                    'parent',
                    InputArgument::OPTIONAL,
                    'parent category if any'
                ),
                new InputArgument(
                    'weight',
                    InputArgument::OPTIONAL,
                    'weight to give'
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

        $name   = $input->getArgument('name');
        $weight = (int)$input->getArgument('weight');
        $empty_weight = $input->getArgument('weight');
        $type = $input->getArgument('type');
        $parent = $input->getArgument('parent');

        if ($weight === 0 && !empty($empty_weight) && $empty_weight !== '0') {
            throw new \Exception("weight must be a number (" . $input->getArgument('weight') . ")");
        }

        $conditions = array('name' => $name, 'type' => $type);
        $category = $this->repo->findOneBy($conditions);

        if ($category) {
            throw new \Exception("The category already exists");
        }

        $cat = new Category();

        if ($parent) {
            $conditions = array('$or' => array(
                array('name' => $parent),
                array('id' => $parent),
                array('id' => new \MongoID($parent)),
            ));
            $pcategory = $this->repo->findOneBy($conditions);
            if (!$pcategory) {
                throw new \Exception("The parent category (". $parent .") does not exists");
            }
            $cat->setParent($pcategory);
        }

        $cat->setName($name);
        $cat->setType($type);
        $cat->setWeight($weight);
        $this->dm->persist($cat);
        $this->dm->flush();
        echo "Added $name\n";
    }
}
