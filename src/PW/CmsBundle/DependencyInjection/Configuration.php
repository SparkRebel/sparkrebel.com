<?php

namespace PW\CmsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('pw_cms');

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('page_controller')
                    ->defaultValue('%pw_cms.page.controller.default%')
                    ->cannotBeEmpty()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
