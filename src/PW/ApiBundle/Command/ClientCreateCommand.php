<?php

namespace PW\ApiBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use OAuth2\OAuth2;

/**
 * @author Chris Jones <leeked@gmail.com>
 */
class ClientCreateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('api:client:create')
            ->setDescription('Creates a new API Client')
            ->setDefinition(array(
                new InputArgument('name', InputArgument::REQUIRED, "Client's name"),
                new InputOption('redirect-uri', '-r', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Redirect URI for Client'),
                new InputOption('allowed-grant-type', '-g', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Allowed Grant Types for Client', array(OAuth2::GRANT_TYPE_AUTH_CODE))
            ));
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* @var $clientManager \FOS\OAuthServerBundle\Model\ClientManager */
        $clientManager = $this->getContainer()->get('fos_oauth_server.client_manager.default');

        /* @var $client \FOS\OAuthServerBundle\Model\Client */
        $client = $clientManager->createClient();
        $client->setName($input->getArgument('name'));
        $client->setRedirectUris($input->getOption('redirect-uri'));
        $client->setAllowedGrantTypes($input->getOption('allowed-grant-type'));

        // Save
        $clientManager->updateClient($client);

        if ($client->getPublicId()) {
            $output->writeln("\n<info>Client created successfully!</info>\n");
            $output->writeln("Name:\n  <comment>{$client->getName()}</comment>");
            $output->writeln("Public ID:\n  <comment>{$client->getPublicId()}</comment>");
            $output->writeln("Grant Types:");
            foreach ($client->getAllowedGrantTypes() as $grantType) {
                $output->writeln("  <comment>{$grantType}</comment>");
            }
        } else {
            $output->writeln('');
            $output->writeln("<error>Unable to create client: {$client->getName()}</error>");
        }
    }
}