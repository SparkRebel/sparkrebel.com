<?php

namespace PW\PostBundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root('pw_post');

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('stream_page_size')
                    ->defaultValue(20)
                ->end()
                ->scalarNode('post_on_facebook_by_default')
                    ->defaultTrue()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
