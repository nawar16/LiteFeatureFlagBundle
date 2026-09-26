<?php

namespace Nawar16\LiteFeatureFlagBundle\Tests\Integration;

use Nawar16\LiteFeatureFlagBundle\Checker\FeatureChecker;
use Nawar16\LiteFeatureFlagBundle\Context\FeatureContext;
use Nawar16\LiteFeatureFlagBundle\DependencyInjection\Compiler\RegisterResolversPass;
use Nawar16\LiteFeatureFlagBundle\Resolver\FeatureResolverInterface;
use Nawar16\LiteFeatureFlagBundle\Storage\ConfigFeatureStorage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class LowPriorityResolverMock implements FeatureResolverInterface {
    public function resolve(string $f, FeatureContext $c): ?bool { return false; }
    public function priority(): int { return 10; }
}
class HighPriorityResolverMock implements FeatureResolverInterface {
    public function resolve(string $f, FeatureContext $c): ?bool { return true; }
    public function priority(): int { return 100; } 
}
class RegisterResolversPassTest extends TestCase
{
    public function testCompilerPassSortsResolversUsingNativePriorityMethod(): void
    {
        $container = new ContainerBuilder();
        $storageDefinition = new Definition(ConfigFeatureStorage::class, [[]]);
        $container->setDefinition(ConfigFeatureStorage::class, $storageDefinition);
        $container->register(FeatureChecker::class)
            ->setArguments([new Reference(ConfigFeatureStorage::class)])
            ->setPublic(true);
            
        //$container->register(FeatureChecker::class)->setArguments([[]]) ->setPublic(true);
        $container->register('resolver.low', LowPriorityResolverMock::class)
            ->setClass(LowPriorityResolverMock::class)->addTag('feature_flag.resolver');
        $container->register('resolver.high', HighPriorityResolverMock::class)
            ->setClass(HighPriorityResolverMock::class)->addTag('feature_flag.resolver');
        $pass = new RegisterResolversPass();
        $pass->process($container);
        $container->compile();
        /** @var FeatureChecker $checker */
        $checker = $container->get(FeatureChecker::class);
        $this->assertTrue($checker->isEnabled('any_feature'));
    }
}
