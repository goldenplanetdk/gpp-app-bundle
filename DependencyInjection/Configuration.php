<?php

namespace GoldenPlanet\GPPAppBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('golden_planet_gpp_app');

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.
        $rootNode
            ->children()
                ->arrayNode('api')
                    ->children()
                        ->scalarNode('app_key')->end()
                        ->scalarNode('app_secret')->end()
                        ->scalarNode('app_scope')->end()
                    ->end()
                ->end() // api
                ->arrayNode('app')
                    ->children()
                        ->scalarNode('redirect_url')->end()
                        ->scalarNode('uninstall_url')->end()
                    ->end()
                ->end() // app
            ->end()
        ;
        return $treeBuilder;
    }
}
