<?php

namespace Zeliard91\Bundle\DynamoDBConnectorBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see 
 * {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('zeliard91_dynamo_db_connector');

        $rootNode
            ->children()
            ->scalarNode('key')->isRequired()->end()
            ->scalarNode('secret')->isRequired()->end()
            ->scalarNode('region')->defaultValue('us-east-1')->end()
            ->scalarNode('base_url')->defaultValue(null)->end()
            ->arrayNode('entity_namespaces')
                ->prototype('scalar')->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
