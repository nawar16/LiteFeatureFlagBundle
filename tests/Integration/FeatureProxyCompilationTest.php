<?php

namespace Nawar16\LiteFeatureFlagBundle\Tests\Integration;

use Nawar16\LiteFeatureFlagBundle\Attribute\Feature;
use Nawar16\LiteFeatureFlagBundle\Checker\FeatureChecker;
use Nawar16\LiteFeatureFlagBundle\DependencyInjection\Compiler\FeatureProxyPass;
use Nawar16\LiteFeatureFlagBundle\Exception\FeatureDisabledException;
use Nawar16\LiteFeatureFlagBundle\Proxy\FeatureProxy;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Nawar16\LiteFeatureFlagBundle\Storage\ConfigFeatureStorage;
use Symfony\Component\DependencyInjection\Definition;

#[Feature('new_payment')]
final class PaymentService {
    public function charge(): string { return 'charged';}
}
#[Feature(name: 'beta_checkout', disabled: Feature::STRATEGY_FALLBACK, fallback: 'disabledFallbackMethod')]
final class CheckoutServiceWithFallback {
    public function process(): string { return 'new';}
    public function disabledFallbackMethod(): string { return 'fallback';}
}
final class StandardUnflaggedService {
    public function run(): string { return 'normal'; }
}

class FeatureProxyCompilationTest extends TestCase
{
    private function createCompiledContainer(array $flags): ContainerBuilder
    {
        $container = new ContainerBuilder();

        $container->setParameter('lite_feature_flag.flags', $flags);
        $container->register(ConfigFeatureStorage::class)
            ->setArguments([$flags]);
        $container->register(FeatureChecker::class)
            ->setArguments([new Reference(ConfigFeatureStorage::class),[]])->setPublic(true);


        $container->register('payment_service', PaymentService::class)->setPublic(true);
        $container->register('fallback_service', CheckoutServiceWithFallback::class)->setPublic(true);
        $container->register('standard_service', StandardUnflaggedService::class)->setPublic(true);
        $container->addCompilerPass(new FeatureProxyPass());
        $container->compile();
        foreach (['payment_service', 'fallback_service'] as $serviceId) {
            if ($container->hasDefinition($serviceId)) {
                $definition = $container->getDefinition($serviceId);
                $arguments = $definition->getArguments();
                if (isset($arguments[0]) && $arguments[0] instanceof Reference) {
                    $innerId = (string) $arguments[0];
                    $definition->setArgument(0, fn() => $container->get($innerId));
                }
            }
        }
        return $container;
    }

    public function testMilestoneEnabledFeatureReturnsValue(): void
    {
        $container = $this->createCompiledContainer(['new_payment' => true]);
        $service = $container->get('payment_service');
        $this->assertSame('charged', $service->charge());
    }
    public function testMilestoneDisabledFeatureThrowsException(): void
    {
        $container = $this->createCompiledContainer(['new_payment' => false]);
        $service = $container->get('payment_service');
        $this->expectException(FeatureDisabledException::class);
        $service->charge();
    }
    public function testDisabledFeatureRoutesToFallbackMethod(): void
    {
        $container = $this->createCompiledContainer(['beta_checkout' => false]);
        $service = $container->get('fallback_service');
        $this->assertSame('fallback', $service->process());
    }
    public function testUnflaggedServiceIsUntouched(): void
    {
        $container = $this->createCompiledContainer(['new_payment' => true]);
        $service = $container->get('standard_service');
        $this->assertInstanceOf(StandardUnflaggedService::class, $service);
        $this->assertSame('normal', $service->run());
    }
}
