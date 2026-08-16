<?php

namespace Nawar16\LiteFeatureFlagBundle\Tests\Integration;

use Nawar16\LiteFeatureFlagBundle\Attribute\Feature;
use Nawar16\LiteFeatureFlagBundle\Checker\FeatureChecker;
use Nawar16\LiteFeatureFlagBundle\DependencyInjection\Compiler\FeatureProxyPass;
use Nawar16\LiteFeatureFlagBundle\Exception\FeatureDisabledException;
use Nawar16\LiteFeatureFlagBundle\Proxy\FeatureProxy;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
#[Feature('new_checkout')]
class MockCheckoutService { public function process(): string { return 'success'; } }
class MockNormalService { public function doWork(): string { return 'working'; } }

class ProxyIntegrationTest extends TestCase
{
    private function buildTestContainer(array $configuredFlags): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->register(FeatureChecker::class)->setArguments([$configuredFlags])->setPublic(true);
        $container->register('app.checkout_service', MockCheckoutService::class)->setPublic(true);
        $container->register('app.normal_service', MockNormalService::class)->setPublic(true);
        $pass = new FeatureProxyPass();
        $pass->process($container);
        $container->compile();
        return $container;
    }
    public function testEnabledFeatureCallsRealService(): void
    {
        $container = $this->buildTestContainer(['new_checkout' => true]);
        $service = $container->get('app.checkout_service');
        $this->assertInstanceOf(FeatureProxy::class, $service);
        $this->assertSame('success', $service->process());
    }
    public function testDisabledFeatureThrowsException(): void
    {
        $container = $this->buildTestContainer(['new_checkout' => false]);
        $service = $container->get('app.checkout_service');
        $this->assertInstanceOf(FeatureProxy::class, $service);
        $this->expectException(FeatureDisabledException::class);
        $this->expectExceptionMessage('The feature "new_checkout" is currently disabled.');
        $service->process();
    }
    public function testUnflaggedServiceIsUntouched(): void
    {
        $container = $this->buildTestContainer(['new_checkout' => true]);
        $service = $container->get('app.normal_service');
        //normal services must never be wrapped/modified
        $this->assertInstanceOf(MockNormalService::class, $service);
        $this->assertNotInstanceOf(FeatureProxy::class, $service);
        $this->assertSame('working', $service->doWork());
    }
}
