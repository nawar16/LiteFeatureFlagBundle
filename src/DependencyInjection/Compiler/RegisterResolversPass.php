<?php

namespace Nawar16\LiteFeatureFlagBundle\DependencyInjection\Compiler;

use Nawar16\LiteFeatureFlagBundle\Checker\FeatureChecker;
use Nawar16\LiteFeatureFlagBundle\Resolver\FeatureResolverInterface;
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
        $resolverData = [];
        foreach ($taggedServices as $id => $tags) {
            $definition = $container->getDefinition($id);
            $class = $definition->getClass();
            if (!$class || !class_exists($class)) continue;
            if (!is_subclass_of($class, FeatureResolverInterface::class)) 
                throw new \InvalidArgumentException(sprintf('Service "%s" must implement %s', $id, FeatureResolverInterface::class));
            $reflection = new \ReflectionClass($class);
            $instance = $reflection->newInstanceWithoutConstructor();
            $priority = $instance->priority();
            $resolverData[] = [
                'priority' => $priority,
                'reference' => new Reference($id)
            ];
        }
        usort($resolverData, function (array $a, array $b): int {return $b['priority'] <=> $a['priority'];});
        $sortedResolvers = array_column($resolverData, 'reference');
        if (!empty($sortedResolvers)) 
            $checkerDefinition->addMethodCall('setResolvers', [$sortedResolvers]);
    }
}
