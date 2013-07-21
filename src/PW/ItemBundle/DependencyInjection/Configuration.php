<?php

namespace PW\ItemBundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root('pw_item');

        $rootNode
            ->children()
                ->arrayNode('feeds')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('assetsdir')->defaultValue('/var/feeds/assets')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
