<?php

namespace Nawar16\LiteFeatureFlagBundle\DependencyInjection\Compiler;

use Nawar16\LiteFeatureFlagBundle\Checker\FeatureChecker;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class RegisterResolversPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(FeatureChecker::class)) return;
        $checkerDefinition = $container->getDefinition(FeatureChecker::class);
        $taggedServices = $container->findTaggedServiceIds('feature_flag.resolver');
        $sortedResolvers = [];
        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $attributes) {
                $priority = $attributes['priority'] ?? 0;
                $sortedResolvers[$priority][] = new Reference($id);
            }
        }
        if (!empty($sortedResolvers)) {
            krsort($sortedResolvers);
            $sortedResolvers = array_merge(...$sortedResolvers);
            $checkerDefinition->addMethodCall('setResolvers', [$sortedResolvers]);
        }
    }
}
