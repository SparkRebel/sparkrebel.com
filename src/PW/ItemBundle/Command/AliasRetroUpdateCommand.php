<?php

namespace PW\ItemBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\ArrayInput,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;

/**
 * apply alias on existing items
 */
class AliasRetroUpdateCommand extends ContainerAwareCommand
{

    protected $dm;

    protected $output;

    protected $aliasRepo;

    protected $itemRepo;

    /**
     * configure
     */
    protected function configure()
    {
        $this
            ->setName('alias:retro:update')
            ->setDescription('apply alias on existing items')
            ->setDefinition(array(
                new InputArgument(
                    'alias',
                    InputArgument::REQUIRED,
                    'The alias to apply. * for all'
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
        $this->dm = $this->getContainer()
            ->get('doctrine_mongodb.odm.document_manager');
        $this->aliasRepo = $this->dm->getRepository('PWItemBundle:Alias');
        $this->itemRepo = $this->dm->getRepository('PWItemBundle:Item');
        $this->output = $output;

        $alias = $input->getArgument('alias');
        if ($alias === '*') {
            $aliases = $this->aliasRepo->find(array());
            $this->output->write("processing {$aliases->count()} aliases\n");

            foreach ($aliases as $aliasDoc) {
                $this->processAlias($aliasDoc->getId(), $aliasDocs->getSynonyms());
            }
        } else {
            $aliasDoc = $this->aliasRepo->find($alias);
            if (!$alias) {
                throw new \Exception("can't find aliases for: $alias");
            }

            $this->processAlias($aliasDoc->getId(), $aliasDoc->getSynonyms());
        }

    }


    /**
     * processAlias
     *
     * @param string $name    system name
     * @param array  $aliases alternate names for this store/brand
     *
     */
    protected function processAlias($name, $aliases = null)
    {
        $this->output->write("processing aliases for $name:\n");

        $aliases = array_map("strtolower", $aliases);
        $aliasesRegex = array();

        foreach ($aliases as $alias) {
            $aliasesRegex []= new \MongoRegex("/^".addslashes($alias)."/i");
        }

        $conditions = array('$or' =>
            array(
                array( 'brandName' => array('$in' => $aliasesRegex) ),
                array( 'merchantName' => array('$in' => $aliasesRegex) ),
            )
        );

        $count = 0;
        $limit = 100;

        while (true) {
            $items = $this->itemRepo->findBy($conditions)
                ->sort(array('feedId' => 'asc'))
                ->limit($limit);
            $id = null;

            foreach ($items as $item) {
                $brandName = strtolower($item->getBrandName());
                if (in_array($brandName, $aliases)) {
                    $item->setBrandName($name);
                }
                $merchantName = strtolower($item->getMerchantName());
                if (in_array($merchantName, $aliases)) {
                    $item->setMerchantName($name);
                }
                $this->dm->persist($item);
                $id = $item->getFeedId();
                $count++;
            }
            $this->output->write("\t$count\n");

            $this->dm->flush();

            if (!$id) {
                break;
            }

            $conditions['feedId']['$gt'] = $id;
        }

        $this->dm->flush();

        $this->output->write("\tfixed and saved\n");
    }

}
