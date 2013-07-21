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
class CategoriesRenameCommand extends ContainerAwareCommand
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
            ->setName('category:rename')
            ->setDescription('Set category rename')
            ->setDefinition(array(
                new InputArgument(
                    'category',
                    InputArgument::REQUIRED,
                    'category name or id'
                ),
                new InputArgument(
                    'new_name',
                    InputArgument::REQUIRED,
                    'new name to the category'
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
        $new_name = $input->getArgument('new_name');

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

        $category->setName($new_name);
        $this->dm->persist($category);
        $this->dm->flush();
    }
}
