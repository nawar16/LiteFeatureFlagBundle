<?php

namespace Nawar16\LiteFeatureFlagBundle\Tests\Integration;

use Nawar16\LiteFeatureFlagBundle\Attribute\Feature;
use Nawar16\LiteFeatureFlagBundle\Checker\FeatureChecker;
use Nawar16\LiteFeatureFlagBundle\DependencyInjection\Compiler\FeatureProxyPass;
use Nawar16\LiteFeatureFlagBundle\Exception\FeatureDisabledException;
use Nawar16\LiteFeatureFlagBundle\Proxy\FeatureProxy;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

#[Feature('new_checkout')]
class MockCheckoutService
{
    public function process(): string{return 'success';}
}
class MockNormalService
{
    public function doWork(): string{return 'working';}
}

class ProxyIntegrationTest extends TestCase
{
    private function buildTestContainer(array $configuredFlags): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->register(FeatureChecker::class)->setArguments([$configuredFlags])->setPublic(true);
        $container->register('app.checkout_service', MockCheckoutService::class)->setPublic(true);
        $container->register('app.normal_service', MockNormalService::class)->setPublic(true);
        $featureClasses = [];
        foreach ($container->getDefinitions() as $id => $definition) {
            $class = $definition->getClass();
            if ($class && class_exists($class)) {
                $reflection = new ReflectionClass($class);
                if ($reflection->getAttributes(Feature::class)) $featureClasses[$id] = $class;
            }
        }
        $pass = new FeatureProxyPass();
        $pass->process($container);
        foreach ($featureClasses as $id => $targetClass) {
            if (!$container->hasDefinition($id)) continue;
            $definition = $container->getDefinition($id);
            $definition->setFactory([self::class,'proxyFactoryBridge']);
            $definition->setArguments([new Reference($id . '.inner_feature_service'),$targetClass,new Reference(FeatureChecker::class),]);
        }
        $container->compile();
        return $container;
    }

    public static function proxyFactoryBridge(
        object $realService,
        string $targetClass,
        FeatureChecker $checker
    ): FeatureProxy {
        $reflection = new \ReflectionClass($targetClass);
        $attributes = $reflection->getAttributes(Feature::class);
        if (!$attributes) throw new \RuntimeException(sprintf('No Feature attribute found on class %s', $targetClass));
        $attribute = $attributes[0]->newInstance();
        return new FeatureProxy($realService,$checker,$attribute);
    }
    public function testEnabledFeatureCallsRealService(): void
    {
        $container = $this->buildTestContainer(['new_checkout' => true,]);
        $service = $container->get('app.checkout_service');
        $this->assertInstanceOf(FeatureProxy::class, $service);
        $this->assertSame('success', $service->process());
    }
    public function testDisabledFeatureThrowsException(): void
    {
        $container = $this->buildTestContainer(['new_checkout' => false,]);
        $service = $container->get('app.checkout_service');
        $this->assertInstanceOf(FeatureProxy::class, $service);
        $this->expectException(FeatureDisabledException::class);
        $this->expectExceptionMessage('The feature "new_checkout" is currently disabled');
        $service->process();
    }
    public function testUnflaggedServiceIsUntouched(): void
    {
        $container = $this->buildTestContainer(['new_checkout' => true]);
        $service = $container->get('app.normal_service');
        $this->assertInstanceOf(MockNormalService::class, $service);
        $this->assertNotInstanceOf(FeatureProxy::class, $service);
        $this->assertSame('working', $service->doWork());
    }
}