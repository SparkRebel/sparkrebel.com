<?php

namespace PW\ItemBundle\Command;

use PW\ItemBundle\Document\Item,
    Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\NullOutput;

/**
 * Process one specific feed_items entry
 */
class FeedItemStep1Command extends ContainerAwareCommand
{
    protected $dm;
    protected $repos = array();

    protected function configure()
    {
        $this
            ->setName('feed:item:step1')
            ->setDescription('Create/verify main db data')
            ->setDefinition(array(
                new InputArgument(
                    'id',
                    InputArgument::REQUIRED,
                    'The feed-item fid'
                )
            ));
    }

    protected function setupRepos()
    {
        $this->dm = $this->getContainer()->get('doctrine_mongodb.odm.document_manager');

        $this->repos['Alias']     = $this->dm->getRepository('PWItemBundle:Alias');
        $this->repos['Brand']     = $this->dm->getRepository('PWUserBundle:Brand');
        $this->repos['Category']  = $this->dm->getRepository('PWCategoryBundle:Category');
        $this->repos['FeedItem']  = $this->dm->getRepository('PWItemBundle:FeedItem');
        $this->repos['Item']      = $this->dm->getRepository('PWItemBundle:Item');
        $this->repos['Merchant']  = $this->dm->getRepository('PWUserBundle:Merchant');
        $this->repos['Whitelist'] = $this->dm->getRepository('PWItemBundle:Whitelist');
    }

    /**
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setupRepos();

        $id   = $input->getArgument('id');
        $item = $this->processItem($id, $output);

        if ($item === false) {
            return -1;
        }

        if (!$item || !is_object($item)) {
            throw new \RuntimeException("Bad return value from processItem for fid: {$id}");
        }

        $output->writeln("<info>Step #1 complete for:</info> {$id} - {$item->getName()}");
        return true;
    }

    /**
     * Accepts a feed item and converts it into all the objects it represents. Including:
     *  - Brand
     *  - Category
     *  - Merchant
     *  - User
     *  - Item
     *
     * @param \PW\ItemBundle\Document\FeedItem|string $feedItem
     * @return \PW\ItemBundle\Document\Item
     */
    protected function processItem($feedItem, OutputInterface $output = null)
    {
        if (!is_object($feedItem)) {
            $id = $feedItem;
            $feedItem = $this->repos['FeedItem']->findOneBy(array('fid' => $id));
            if (!$feedItem) {
                throw new \Exception("Feed Item {$id} doesn't exist");
            }
        }
        
        if (!$output) {
            $output = new NullOutput();
        }

        $action = $feedItem->getAction();
        $fid = $feedItem->getFid();
        $item = $this->repos['Item']->findOneBy(array('feedId' => $fid));

        if ($action === 'deleted') {
            if (!$item) {
                $output->writeln("<comment>Item is being deleted but doesn't already exist, skipping...</comment>");
                return false;
            }
            $isDeleted = true;
        } else {
            $isDeleted = false;
        }

        $description = $feedItem->getDescription();
        $isOnSale = $feedItem->getOnSale();
        $link = $feedItem->getLink();
        $modified = $feedItem->getModified();
        $name = $feedItem->getName();
        $price = $feedItem->getPrice();
        $priceHistory = $feedItem->getPriceHistory();
        $source = $feedItem->getSource();

        $merchantName = $feedItem->getMerchant();
        $merchantName = $this->checkAliases($merchantName, 'Merchant');
        $merchantUser = $this->getOrCreateApprovedUser($merchantName);
        $output->writeln("<comment>Merchant name:'".$merchantName."', Merchant user get_class:".get_class($merchantUser)."</comment>");

        $brandName = $feedItem->getBrand();
        if (empty($brandName)) {
            $brandName = $merchantName;
        }

        $brandName = $this->checkAliases($brandName, 'Brand');
        $brandUser = $this->getOrCreateApprovedUser($brandName);
        $output->writeln("<comment>Brand name:'".$brandUser."', Brand user id: ".($brandUser?$brandUser->getId():'n/a')."</comment>");

        if ($merchantUser === null && $brandUser === null) {
            $feedItem->setStatus('rejected');
            $this->dm->persist($feedItem);
            $this->dm->flush($feedItem, array('safe' => false, 'fsync' => false));
            $output->writeln("<error>Item <comment>{$fid}</comment> didn't pass whitelist for merchant (" . $feedItem->getMerchant() .") or brand (" . $feedItem->getBrand() . ")</error>");
            return false;
        }

        $categoryNames = $feedItem->getCategories();
        $categories = $this->getCategories($categoryNames);

        if (count($categories) === 0) {
            throw new \Exception("None of the categories for '{$fid}' exist");
        }

        if (!$item) {
            $item = new Item();
            $item->setFeedId($fid);
            $item->setIsActive(false);
        } else {
            $item->setIsActive(!$isDeleted);
        }

        $item->setDescription($description);
        $item->setIsDiscontinued($isDeleted);
        $item->setMerchantName($merchantName);
        if ($merchantUser) {
            $item->setMerchantUser($merchantUser);
        }
        $item->setBrandName($brandName);
        if ($brandUser) {
            $item->setBrandUser($brandUser);
        }
        $item->setIsOnSale($isOnSale);
        $item->setLink($link);
        $item->setName($name);

        //TODO: use real price history
        if ($item->getPrice() && (int) $item->getPrice() != (int) $price) {
            $item->setPricePrevious($item->getPrice());
        }
        //
        $item->setPrice($price);

        // if (count($priceHistory) > 1) {
        //     $prices = array_values($priceHistory);
        //     $item->setPricePrevious($prices[1]);
        // }

        $item->replaceCategories($categories);

        if ($merchantUser) {
            $item->setCreatedBy($merchantUser);
        } else {
            $item->setCreatedBy($brandUser);
        }

        $this->dm->persist($item);
        $this->dm->flush(null, array('safe' => false, 'fsync' => false));

        $feedItem->setStatus('step2');
        $this->dm->persist($feedItem);
        $this->dm->flush(null, array('safe' => false, 'fsync' => false));

        return $item;
    }

    /**
     * Find the canonical version of a name
     *
     * @param string $name to check
     * @param string $type of check, unused but stubbed just in case
     *
     * @return string
     */
    protected function checkAliases($name, $type = 'Merchant')
    {
        if ($this->repos['Alias']->find($name)) {
            return $name;
        }

        $result = $this->repos['Alias']->createQueryBuilder()
            ->field('synonyms')->equals(strtolower($name))
            ->getQuery()
            ->getSingleResult();

        if ($result) {
            return $result->getId();
        }

        return $name;
    }


    /**
     * getOrCreateApprovedUser
     *
     * @param string $name to check
     *
     * @return User user object or null
     */
    protected function getOrCreateApprovedUser($name)
    {
        $userEntry = $this->repos['Whitelist']->find($name);
        if (!$userEntry) {
            return null;
        }

        $repo = $this->repos[ucfirst($userEntry->getType())];
        $user = $repo->findOneBy(array('$or' => array(array('username' => $name), array('alias' => $name))));
        if (!$user) {
            $userType = 'PW\\UserBundle\\Document\\' . ucfirst($userEntry->getType());
            $user = new $userType();
            $user->setEnabled(true);
            $user->setName($name);
            $user->setUsername($name);
            $this->dm->persist($user);
            $this->dm->flush(null, array('safe' => false, 'fsync' => false));
        }

        return $user;
    }


    /**
     * Get all categories by name
     *
     * @param array $names a flat array
     *
     * @return array of Category instances
     */
    protected function getCategories($names)
    {
        $fallback = array();
        $return = array();
        foreach ($names as $name) {
            $category = $this->getCategory($name);
            if (empty($category)) {
                continue;
            }
            if (!$fallback) {
                $fallback = array($category);
            }
            if (!$category->getParent()) {
                continue;
            }
            $return[] = $category;
        }
        if (!$return) {
            return $fallback;
        }
        return $return;
    }

    /**
     * get category by name
     *
     * @param string $name of the category
     *
     * @return Category instance
     */
    protected function getCategory($name)
    {
        return $this->repos['Category']->findOneBy(array('name' => $name));
    }

    /**
     * Get merchant by name
     *
     * @param string $name of the merchant
     *
     * @return Merchant instance
     */
    protected function getMerchant($name)
    {
        return $this->getByName($name, 'Merchant', 'PW\UserBundle\Document\Merchant');
    }

    /**
     * get or create an entry by name
     *
     * @param string $name      The name of the entry in the db
     * @param string $class     The class name
     * @param string $className The full classname - required for a quirk of php
     *
     * @return Doctrine instance of class $className
     */
    protected function getByName($name, $class, $className)
    {
        $instance = $this->repos[$class]->findOneBy(array('name' => $name));
        return $instance;
    }

}
