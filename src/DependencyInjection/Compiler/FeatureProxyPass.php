<?php

namespace Nawar16\LiteFeatureFlagBundle\DependencyInjection\Compiler;

use Nawar16\LiteFeatureFlagBundle\Attribute\Feature;
use Nawar16\LiteFeatureFlagBundle\Checker\FeatureChecker;
use Nawar16\LiteFeatureFlagBundle\Proxy\FeatureProxy;
use Nawar16\LiteFeatureFlagBundle\Proxy\FeatureProxyFactory;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;

class FeatureProxyPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if(!$container->hasDefinition(FeatureChecker::class)) return;
        foreach ($container->getDefinitions() as $id => $definition) {
            $class = $definition->getClass();
            if (!$class || !class_exists($class)) continue;
            $reflectionClass = new ReflectionClass($class);
            $attributes = $reflectionClass->getAttributes(Feature::class);
            if (empty($attributes)) continue;
            /** @var Feature $featureAttribute */
            $featureAttribute = $attributes[0]->newInstance();
            $featureName = $featureAttribute->name;
            //backup to prevent collisions
            $innerServiceId = $id . '.inner_feature_service';
            $container->setDefinition($innerServiceId, clone $definition);
            //definition pointing to the Proxy as id
            $proxyDefinition = new Definition(FeatureProxy::class);
            $proxyDefinition->setFactory([FeatureProxyFactory::class, 'createProxy']);
            // $proxyDefinition->setArguments([
            //     $container->getDefinition($innerServiceId),
            //     $container->getDefinition(FeatureChecker::class),
            //     $featureName
            // ]);
            // $proxyDefinition->setArguments([
            //     new Definition(null, [new Reference($innerServiceId)]), 
            //     $container->getDefinition(FeatureChecker::class),
            //     $featureName
            // ]);
            $proxyDefinition->setArguments([
                new ServiceClosureArgument(new Reference($innerServiceId)),
                new Reference(FeatureChecker::class),
                $featureName,
            ]);
            $proxyDefinition->setPublic($definition->isPublic());
            $proxyDefinition->setShared($definition->isShared());
            $container->setDefinition($id, $proxyDefinition);
        }
    }
}
