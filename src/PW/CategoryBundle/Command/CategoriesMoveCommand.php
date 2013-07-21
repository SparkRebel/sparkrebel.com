<?php

namespace PW\CategoryBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    PW\CategoryBundle\Document\Category;

class CategoriesMoveCommand extends ContainerAwareCommand
{
    /**
     * @var type
     */
    protected $dm;

    /**
     * configure
     */
    protected function configure()
    {
        $this
            ->setName('category:move')
            ->setDescription('Move all boards/posts from one category to another')
            ->setDefinition(array(
                new InputArgument('from', InputArgument::REQUIRED, 'Category name/id to move from'),
                new InputArgument('to', InputArgument::REQUIRED, 'Category name/id to move to'),
                new InputArgument('type', InputArgument::OPTIONAL, 'Category type', 'user'),
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
        $this->dm = $this->getContainer()->get('doctrine_mongodb.odm.document_manager');
        $type = $input->getArgument('type');

        $from = $input->getArgument('from');
        $qb = $this->dm->createQueryBuilder('PWCategoryBundle:Category')
            ->field('type')->equals($type);
        $qb->addOr($qb->expr()->field('_id')->equals(new \MongoId($from)));
        $qb->addOr($qb->expr()->field('name')->equals($from));
        $fromCategory = $qb->getQuery()->getSingleResult();

        if (!$fromCategory) {
            throw new \Exception('No Category found to move from with name or _id: ' . $from);
        }

        $to = $input->getArgument('to');
        $qb = $this->dm->createQueryBuilder('PWCategoryBundle:Category')
            ->field('type')->equals($type);
        $qb->addOr($qb->expr()->field('_id')->equals(new \MongoId($to)));
        $qb->addOr($qb->expr()->field('name')->equals($to));
        $toCategory = $qb->getQuery()->getSingleResult();

        if (!$toCategory) {
            throw new \Exception('No Category found to move to with name or _id: ' . $from);
        }

        foreach (array('PWBoardBundle:Board', 'PWPostBundle:Post') as $type) {
            $this->updateType($output, $type, $fromCategory, $toCategory);
        }
    }

    /**
     * @param OutputInterface $output
     * @param string $type
     */
    public function updateType(OutputInterface $output, $type, Category $fromCategory, Category $toCategory)
    {
        $parts = explode(':', $type);
        $name  = $parts[1];

        $beforeCounts = $this->getCounts($type, $fromCategory, $toCategory);

        $output->writeln('');
        if ($beforeCounts['from'] > 0) {
            $output->writeln("Processing {$beforeCounts['from']} {$name}(s)...");

            $result = $this->dm->createQueryBuilder($type)
                ->findAndUpdate()
                ->field('category.$id')->equals(new \MongoId($fromCategory->getId()))
                ->update()
                ->field('category.$id')->set(new \MongoId($toCategory->getId()))
                ->getQuery(array('multiple' => true))->execute();

            $afterCounts = $this->getCounts($type, $fromCategory, $toCategory);
            $output->writeln(sprintf("<info>Successfully updated %d {$name}(s)</info>", $afterCounts['to'] - $beforeCounts['to']));

            if (!$result) {
                $output->writeln("<error>An error occurred while processing {$name}(s).</error>");
            }
        } else {
            $output->writeln(sprintf("<comment>No {$name}(s) found with Category: %s</comment>", $fromCategory->getName()));
        }
    }

    /**
     * @param string $type
     * @param Category $fromCategory
     * @param Category $toCategory
     * @return array
     */
    public function getCounts($type, Category $fromCategory, Category $toCategory)
    {
        $total = array('from' => 0, 'to' => 0,);

        $total['from'] = $this->dm->createQueryBuilder($type)
            ->field('category.$id')->equals(new \MongoId($fromCategory->getId()))
            ->count()->getQuery()->execute();

        $total['to'] = $this->dm->createQueryBuilder($type)
            ->field('category.$id')->equals(new \MongoId($toCategory->getId()))
            ->count()->getQuery()->execute();

        return $total;
    }
}
