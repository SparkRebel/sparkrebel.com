<?php

namespace PW\CMSBundle\Command;

use PW\CMSBundle\Document\Page,
    Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;

/**
 * PageCreateCommand
 */
class PageCreateCommand extends ContainerAwareCommand
{
    /**
     * document manager placeholder instance
     */
    protected $dm;

    /**
     * page manager placeholder instance
     */
    protected $pageManager;

    /**
     * configure
     */
    protected function configure()
    {
        $this
            ->setName('cms:page:create')
            ->setDescription('Create a static page')
            ->setDefinition(array(
                new InputArgument(
                    'title',
                    InputArgument::REQUIRED,
                    'page title'
                ),
                new InputArgument(
                    'url',
                    InputArgument::REQUIRED,
                    'page url'
                ),
                new InputArgument(
                    'content',
                    InputArgument::OPTIONAL,
                    'page content'
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
        $this->pageManager = $this->getContainer()
            ->get('pw_cms.page_manager');

        $data = array(
            'title' => $input->getArgument('title'),
            'url' => $input->getArgument('url'),
            'content' => $input->getArgument('content')  ,
        );

        $page = $this->pageManager->create($data);
        if($page) {
            $page->setIsActive(true);
            $this->dm->persist($page);
            $this->dm->flush();
            $output->write("page created with id: " . $page->getId() . "\n");
        } else {
            throw new \Exception("page creation failed");
        }

    }

}
