<?php

namespace PW\CategoryBundle\Command;

use PW\CategoryBundle\Document\Category,
    Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;

class CategorySeedCommand extends ContainerAwareCommand
{
    /**
     * document manager placeholder instance
     */
    protected $dm;

    protected $renamedCategories = array(
        'makeup & perfume' => array(
            'type' => 'item',
            'newname' => 'beauty'
        )
    );

    protected $itemCategories = array(
        'tops' => array(
            'hoodies & sweatshirts',
            'shirts & blouses',
            'sweaters',
            'tanks & camis',
            't-shirts & polos'
        ),
        'bottoms' => array(
            'jeans',
            'leggings',
            'pants',
            'shorts & capris',
            'skirts',
            'jumpsuits & rompers'
        ),
        'dresses' => array(),
        'outerwear' => array(
            'coats',
            'jackets',
            'vests'
        ),
        'accessories' => array(
            'bags',
            'belts',
            'scarves',
            'hats',
            'sunglasses',
            'gloves',
            'hair accessories'
        ),
        'jewelry' => array(
            'necklaces',
            'bracelets',
            'rings',
            'earrings',
            'watches',
            'pins'
        ),
        'shoes' => array(
            'boots',
            'clogs',
            'flats',
            'flip flops',
            'heels',
            'sandals',
            'slippers',
            'sneakers'
        ),
        'swimwear' => array(),
        'makeup & perfume' => array(
            'eyes',
            'lips',
            'face',
            'perfume',
            'nailpolish',
            'hair products'
        ),
        'intimates & hosiery' => array(
            'bras',
            'corsets & shapewear',
            'panties',
            'socks & hosiery',
            'pajamas & lingerie',
        ),
        'gifts' => array()

    );

    protected $userCategories = array(
        'Apparel',
        'Beauty',
        'Celeb Style & Red Carpet',
        'DIY Fashion & Beauty',
        'Gifts & Wish Lists',
        'Fashion Photos',
        'Fitness & Sports',
        'Formal',
        'Inspiration & Quotes',
        'Jewelry',
        'Look for Less',
        'Outfits',
        'Maternity Style',
        'Runway & Designers',
        'Accessories',
        'Style Articles & Tips',
        'Wedding',
        'Shoes',
        'Fashion Disasters',
        'Promos',
        'Other',
    );

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

    protected function configure()
    {
        $this->setName('category:seed')
            ->setDescription('Create our standard categoriesegories');
    }

    /**
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->dm = $this->getContainer()->get('doctrine_mongodb.odm.document_manager');
        $this->repo = $this->dm->getRepository('PWCategoryBundle:Category');

        $output->write("User categories:\n");
        foreach ($this->userCategories as $category) {
            $output->write("\t$category\n");
            $this->find('user', $category);
        }

        $output->write("\n");
        $output->write("Item categories:\n");
        foreach ($this->itemCategories as $category => $subCats) {
            $parent = $this->find('item', $category);
            $output->write("\t$category\n");
            foreach ($subCats as $subCat) {
                $output->write("\t\t$subCat\n");
                $this->find('item', $subCat, $parent);
            }
        }

    }

    /**
     * find
     *
     * @param string   $type   the type of category - item or user
     * @param string   $name   category to find
     * @param Category $parent object - if there is one
     *
     * @return category instance
     */
    protected function find($type, $name, $parent = null)
    {
        $cat = $this->repo->findOneBy(
            array(
                'name' => $name,
                'type' => $type
            )
        );

        if (!$cat) {
            $cat = new Category();
            $cat->setType($type);
            $cat->setName($name);
        } else {
            if (!empty($this->renamedCategories[$name]) && $this->renamedCategories[$name]['type'] == $type) {
                $cat->setName($this->renamedCategories[$name]['newname']);
            }
        }

        if ($parent && $cat->getParent() !== $parent) {
            $cat->setParent($parent);
        }
        $this->dm->persist($cat);
        $this->dm->flush();

        return $cat;
    }
}
