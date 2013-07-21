<?php

namespace PW\ItemBundle\Command;

use PW\ItemBundle\Document\Alias,
    Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;

/**
 * SeedAliasesCommand
 */
class SeedAliasesCommand extends ContainerAwareCommand
{
    /**
     * document manager placeholder instance
     */
    protected $dm;

    protected $renames = array(
        'bebe.com' => 'Bebe'
    );

    protected $names = array(
        'bebe.com' => array(
            'bebe',
            'bebe sport',
            '2b',
            'bebe.com'
        ),
        'BCBGMAXAZRIA' => array(
            'Max Azria',
            'BCB Generation'
        ),
        'American Eagle Outfitters' => array(
            'American Eagle Outfitter',
            'Aerie'
        ),
        'Buckle.com' => array(
            'Bukle',
            'BKE'
        ),
        'Calvin Klein' => array(
            'Calvin Klein Underwear',
            'CK Jeans',
            'CK'
        ),
        'Donna Karen' => array(
            'DKNY',
            'DKNYC',
            'Donna Karen Hosiery',
            'Donna Karen New York',
            'Donna Karen Intimates'
        ),
        'Dolce Vita' => array(
            'DV by Dolce Vita'
        ),
        'Eileen Fisher' => array(
            'Eileen Fisher intimates',
            'Eileen Fisher Petites'
        ),
        'Ella Moss' => array(
            'Ella Moss Maternity',
            'Ella Moss (VF Contemporary)'
        ),
        'Elle Macpherson' => array(
            'Elle Macpherson Intimates'
        ),
        'Erin Fetherston' => array(
            'ERIN Erin Fetherston'
        ),
        'French Connection' => array(
            'FCUK',
            'French Connection (US)'
        ),
        'GUESS' => array(
            'G by Guess',
            'Guess Watches',
            'Guess by Marciano'
        ),
        'James Jeans' => array(
            'James Jeans Plus'
        ),
        'Joe\'s Jeans' => array(
            'The Tee by Joe\'s'
        ),
        'Joie' => array(
            'Joie a la Plage',
            'Soft by Joie'
        ),
        'Kensie' => array(
            'Kensie.com'
        ),
        'Lafayette 148 New York' => array(
            'Lafayette 148 New York Plus'
        ),
        'LaRok' => array(
            'LaRok Luxe'
        ),
        'Lucky Brand' => array(
            'Lucky Brand Jeans'
        ),
        'Macy\'s' => array(
          'macys.com'
        ),
        'Marc Jacobs' => array(
            'Marc by Marc Jacobs'
        ),
        'Micheal Kors' => array(
            'Michael by Michael Kors',
            'Michael by Michael Kors Petites',
            'Michael by Michael Kors Plus'
        ),
        'ModCloth' => array(
            'ModCloth.com'
        ),
        'Nanette Lepore' => array(
            'Oonagh by Nanette Lepore'
        ),
        'Nike' => array(
            'Nike Plus'
        ),
        'Not Your Daughter\'s Jeans' => array(
            'Not Your Daughter\'s Jeans Plus',
            'Not Your Daughter\'s Jeans Petites'
        ),
        'Paige Denim' => array(
            'Paige Maternity'
        ),
        'Pacific Sunwear' => array(
          'Pacific Sunwear Affiliate Program'
        ),
        'Rachel Roy' => array(
            'RACHEL Rachel Roy'
        ),
        'Ralph Lauren' => array(
            'Denim & Supply Ralph Lauren',
            'Polo Ralph Lauren',
            'RLX Ralph Lauren',
            'Ralph Lauren Hosiery',
            'Lauren by Ralph Lauren Dress',
            'Lauren by Ralph Lauren Petites',
            'Ralph Lauren Black Label',
            'Ralph Lauren Blue Label',
            'Ralph Lauren Golf'
        ),
        'Splendid' => array(
            'Splendid Maternity',
            'Splendid (VF Contemporary)'
        ),
        'Steve Madden' => array(
            'Steven',
            'Big Buddha'
        ),
        'Target' => array(
            'Target.com'
        ),
        'Tahari' => array(
            'T Tahari'
        ),
        'Threadless' => array(
            'threadlesscom'
        ),
        'Urban Outfitters' => array(
            'Kimichi Blue'
        ),
        'Vans' => array(
            'Vans,a Division of VF Outdoor, Inc.',
            'VANS'
        ),
        'Zac Posen' => array(
            'Z Spoke Zac Posen'
        ),
        'Benefit' => array(
            'Benefit Cosmetics'
        ),
        'L\'Oreal' => array(
            'L\'Oreal Paris'
        )
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
            ->setName('seed:aliases')
            ->setDescription('Create our list of brand aliases')
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
        $this->repo = $this->dm->getRepository('PWItemBundle:Alias');

        foreach ($this->names as $canonical => $aliases) {
            $output->write("\t$canonical\n");
            $this->find($canonical, $aliases);
        }
    }

    /**
     * find
     *
     * @param string $name    category to find
     * @param array  $aliases alternate names for this store/brand
     *
     * @return category instance
     */
    protected function find($name, $aliases = null)
    {
        $row = $this->repo->find($name);

        $aliases = array_map("strtolower", $aliases);

        if ($row) {
            $aliases = array_unique(
                        array_merge(
                            $aliases,
                            array_map("strtolower", $row->getSynonyms())
                        )
                    );

            if (!empty($this->renames[$name])) {
                $this->dm->remove($row);
                $this->dm->flush();
                $row = new Alias();
                $row->setId($this->renames[$name]);
            }

        } else {
            $row = new Alias();
            $row->setId($name);
        }


        $row->setSynonyms($aliases);

        $this->dm->persist($row);
        $this->dm->flush();

        return $row;
    }
}
