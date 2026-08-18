<?php

namespace Nawar16\LiteFeatureFlagBundle\DependencyInjection\Compiler;

use Nawar16\LiteFeatureFlagBundle\Attribute\Feature;
use Nawar16\LiteFeatureFlagBundle\Checker\FeatureChecker;
use Nawar16\LiteFeatureFlagBundle\Proxy\FeatureProxy;
use Nawar16\LiteFeatureFlagBundle\Proxy\FeatureProxyFactory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use ReflectionClass;

final class FeatureProxyPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(FeatureChecker::class)) return;
        foreach ($container->getDefinitions() as $id => $definition) {
            $class = $definition->getClass();
            if (!$class || !class_exists($class)) continue;
            $reflectionClass = new ReflectionClass($class);
            $attributes = $reflectionClass->getAttributes(Feature::class);
            if (empty($attributes)) continue;
            $firstAttribute = $attributes[0]; 
            $featureAttribute = $firstAttribute->newInstance();
            $innerServiceId = $id . '.inner_feature_service';
            $container->setDefinition($innerServiceId, clone $definition);
            $closureReference = new Reference($innerServiceId, ContainerBuilder::IGNORE_ON_INVALID_REFERENCE);
            $proxyDefinition = new Definition(FeatureProxy::class);
            $proxyDefinition->setFactory([FeatureProxyFactory::class, 'createProxy']);
            $proxyDefinition->setArguments([
                $closureReference, 
                new Reference(FeatureChecker::class),
                new Definition(Feature::class, [
                    $featureAttribute->name,
                    $featureAttribute->disabled,
                    $featureAttribute->fallback
                ])
            ]);
            $proxyDefinition->setPublic($definition->isPublic());
            $proxyDefinition->setShared($definition->isShared());
            $container->setDefinition($id, $proxyDefinition);
        }
    }
}
