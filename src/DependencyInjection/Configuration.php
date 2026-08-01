<?php

namespace Nawar16\LiteFeatureFlagBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('lite_feature_flag_bundle');
        $treeBuilder->getRootNode()->useAttributeAsKey('name')
        ->booleanPrototype()->end();
        return $treeBuilder;
    }
}