<?php

namespace Perform\BaseBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('perform_base');

        $rootNode
            ->fixXmlConfig('extended_entity', 'extended_entities')
            ->children()
                ->arrayNode('assets')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('entrypoints')
                            ->useAttributeAsKey('name')
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('namespaces')
                            ->useAttributeAsKey('name')
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('extra_js')
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('extra_sass')
                            ->prototype('scalar')->end()
                        ->end()
                        ->scalarNode('theme')
                            ->defaultValue('~perform-base/scss/themes/default')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('mailer')
                    ->children()
                        ->scalarNode('from_address')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->arrayNode('excluded_domains')
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('admins')
                    ->prototype('array')
                        ->children()
                            ->arrayNode('types')
                                ->prototype('variable')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('doctrine')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('resolve')
                            ->useAttributeAsKey('interface')
                            ->prototype('variable')
                                ->validate()
                                    ->ifTrue(function ($value) {
                                        if (is_string($value)) {
                                            return false;
                                        }
                                        if (!is_array($value) || empty($value)) {
                                            return true;
                                        }
                                        foreach (array_merge(array_keys($value), array_values($value)) as $item) {
                                            if (!is_string($item)) {
                                                return true;
                                            }
                                        }

                                        return false;
                                    })
                                    ->thenInvalid('Each resolved entity value must be either a class name, or an array of class names with the related entities as keys.')
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('extended_entities')
                    ->useAttributeAsKey('parent')
                    ->prototype('scalar')
                        ->cannotBeEmpty()
                    ->end()
                ->end()
                ->arrayNode('panels')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('left')
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('right')
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('menu')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('simple')
                            ->useAttributeAsKey('alias')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('route')
                                        ->defaultNull()
                                    ->end()
                                    ->scalarNode('entity')
                                        ->defaultNull()
                                    ->end()
                                    ->scalarNode('icon')
                                        ->defaultNull()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('order')
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        // how to configure admin options:
        // perform_base:
        //     admins:
        //         PerformMusicBundle:Composition:
        //             types:
        //                 # change an option
        //                 publishDate:
        //                     options:
        //                         dateFormat: 'Y'
        //                     viewOptions:
        //                         dateFormat: 'Y-m-d'
        //                 # enable a field
        //                 category:
        //                     contexts:
        //                         - list
        //                         - view
        //                         - create
        //                         - edit
        //                 # disable a field
        //                 title:
        //                     contexts: []
        //                 # change a type
        //                 slug:
        //                     type: text
        //                 # add a field (even though it will probably be an
        //                 # extended entity anyway)
        //                 createdAt:
        //                     type: datetime
        //                     contexts:
        //                         - edit

        return $treeBuilder;
    }
}
