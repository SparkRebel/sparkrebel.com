<?php

namespace PW\ApplicationBundle\Command;

use PW\ApplicationBundle\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MenuGenerateSitemapCommand extends AbstractCommand
{
    protected function configure()
    {
        $this->setName('menu:generate:sitemap')
             ->setDescription('Generate web/sitemap.xml')
             ->setDefinition(array(
             ));
    }

    /**
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(date('Y-m-d H:i:s').' Starting <comment>menu:generate:sitemap</comment>');
        
        $sitemap_filename = $this->getContainer()->get('kernel')->getRootdir().'/../web/sitemap.xml';
        $xml = $this->render();
        file_put_contents($sitemap_filename, $xml);

        $output->writeln(date('Y-m-d H:i:s').' Ending <comment>menu:generate:sitemap</comment>');
    }
    
    protected function render()
    {
        $view = "PWApplicationBundle:Default:sitemap.xml.twig";
        $parameters = array();
        return $this->getContainer()->get('templating')->render($view, $parameters);
    }
}
