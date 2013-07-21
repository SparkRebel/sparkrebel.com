<?php

namespace PW\ItemBundle\Command;

use PW\ItemBundle\Document\Whitelist,
    Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;

class SeedWhitelistCommand extends ContainerAwareCommand
{
    /**
     * document manager placeholder instance
     */
    protected $dm;

    protected $merchantNames = array(
        'alloy.com',
        'Bloomingdales',
        'buckle.com',
        'David\'s Bridal',
        'Infinity Shoes',
        'Karmaloop.com',
        'Kohl\'s',
        'Macy\'s',
        'ModCloth',
        'Nordstrom',
        'Pacific Sunwear',
        'Payless Shoes',
        'Pink Mascara',
        'Piperlime',
        'Shopbop.com',
        'Singer22',
        'Target',
        'Threadless',
        'Tilly\'s',
        'Unique Vintage',
        'Urban Outfitters'
    );

    protected $brandNames = array(
        '7 for All Mankind',
        'ABS by Allen Schwartz',
        'Adidas',
        'Aeropostale',
        'Alice & Olivia',
        'American Eagle Outfitters',
        'Bar III',
        'Bare Essentials',
        'BCBGMAXAZRIA',
        'Bebe',
        'Benefit',
        'Betsey Johnson',
        'Bobbie Brown',
        'C&C California',
        'Calvin Klein',
        'Caparros',
        'Catherine Malandrino',
        'Chanel',
        'Chinese Laundry',
        'Citizens of Humanity',
        'Clarins',
        'Clinique',
        'Coach',
        'Cole Haan',
        'Cover Girl',
        'Cynthia Steffe',
        'dELiA*s',
        'DIANA von FURSTENBURG',
        'Dior',
        'Dolce Vita',
        'Donna Karen',
        'Eileen Fisher',
        'Elie Tahari',
        'Elizabeth and James',
        'Elizabeth Arden',
        'Ella Moss',
        'Elle Macpherson',
        'Ellen Tracy',
        'Erin Fetherston',
        'Essie',
        'Estee Lauder',
        'Franco Sarto',
        'Free People',
        'French Connection',
        'Frye',
        'Gap',
        'GUESS',
        'Hale Bob',
        'Hanky Panky',
        'Hard Tail',
        'Haviana',
        'Herve Legere',
        'Hot Topic',
        'J Brand',
        'James Jeans',
        'Jessica Simpson',
        'Joe\'s Jeans',
        'Joie',
        'Josie',
        'Juicy Couture',
        'Kate Spade',
        'Kensie.com',
        'Kiehl\'s',
        'Lacoste',
        'Lafayette 148 New York',
        'Lancome',
        'LaRok',
        'Laundry by Shelli Segal',
        'Lilly Pulitzer',
        'Lucky Brand',
        'L\'Oreal',
        'MAC',
        'Marc Jacobs',
        'Max & Cleo',
        'Michael Kors',
        'Michael Stars',
        'Milly',
        'Miss Me Jeans',
        'Miss Sixty',
        'Nanette Lepore',
        'NARS',
        'Natori',
        'Nicole Miller',
        'Nike',
        'Nine West',
        'Not Your Daughter\'s Jeans',
        'Old Navy',
        'OnGossamer',
        'OPI',
        'Origins',
        'Paige Denim',
        'PJ Salvage',
        'Plenty by Tracy Reese',
        'Rachel Roy',
        'Rachel Zoe',
        'rag & bone',
        'Ralph Lauren',
        'Rampage',
        'Rebecca Minkoff',
        'Rebecca Taylor',
        'Revlon',
        'Shiseido',
        'Shoshanna',
        'skechers',
        'Sketchers',
        'Sky',
        'Smashbox',
        'Sonia Rykiel',
        'Splendid',
        'Steve Madden',
        'T Bags',
        'Tahari',
        'The North Face',
        'Theory',
        'Torrid.com',
        'Tory Burch',
        'Trina Turk',
        'True Religion',
        'Ugg Australia',
        'Urban Decay',
        'VANS',
        'Vans',
        'Wet Seal',
        'Wildfox',
        'XOXO',
        'Yves Saint Laurent',
        'Zac Posen'
    );

    /**
     * repo
     */
    protected $repo;

    /**
     * configure
     */
    protected function configure()
    {
        $this
            ->setName('seed:whitelist')
            ->setDescription('Create our list of approved brand names')
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
        $this->repo = $this->dm->getRepository('PWItemBundle:Whitelist');

        foreach ($this->merchantNames as $name) {
            $output->write("\t$name\n");
            $this->find($name, 'merchant');
        }

        foreach ($this->brandNames as $name) {
            $output->write("\t$name\n");
            $this->find($name, 'brand');
        }

    }

    /**
     * find
     *
     * @param string $name name to find
     * @param string $type type to find
     *
     * @return whitelist instance
     */
    protected function find($name, $type)
    {
        $row = $this->repo->find($name);

        if ($row) {
            return $row;
        }

        $row = new Whitelist();
        $row->setId($name);
        $row->setType($type);

        $this->dm->persist($row);
        $this->dm->flush();

        return $row;
    }
}
