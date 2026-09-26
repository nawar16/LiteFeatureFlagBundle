<?php

namespace Nawar16\LiteFeatureFlagBundle\Tests\Proxy;

use BadMethodCallException;
use Nawar16\LiteFeatureFlagBundle\Attribute\Feature;
use Nawar16\LiteFeatureFlagBundle\Checker\FeatureChecker;
use Nawar16\LiteFeatureFlagBundle\Exception\FeatureDisabledException;
use Nawar16\LiteFeatureFlagBundle\Proxy\FeatureProxy;
use Nawar16\LiteFeatureFlagBundle\Storage\ConfigFeatureStorage;
use PHPUnit\Framework\TestCase;

class MockCheckoutServiceEngine
{
    public function process(): string { return 'new_process_executed'; }
    public function legacyProcess(): string { return 'legacy_fallback_executed'; }
}

class FeatureProxyStrategyTest extends TestCase
{
    public function testExceptionStrategyThrowsWhenDisabled(): void
    {
        $checker = new FeatureChecker(new ConfigFeatureStorage(['new_checkout' => false]));
        $realService = new MockCheckoutServiceEngine();
        $attribute = new Feature(name: 'new_checkout', disabled: Feature::STRATEGY_EXCEPTION);
        $proxy = new FeatureProxy(fn() => $realService, $checker, $attribute);
        $this->expectException(FeatureDisabledException::class);
        $this->expectExceptionMessage('The feature "new_checkout" is currently disabled');
        $proxy->process();
    }
    public function testFallbackStrategyRoutesToAlternativeMethodWhenDisabled(): void
    {
        $checker = new FeatureChecker(new ConfigFeatureStorage(['new_checkout' => false]));
        $realService = new MockCheckoutServiceEngine();
        $attribute = new Feature(name: 'new_checkout', disabled: Feature::STRATEGY_FALLBACK, fallback: 'legacyProcess');
        $proxy = new FeatureProxy(fn() => $realService, $checker, $attribute);
        $this->assertSame('legacy_fallback_executed', $proxy->process());
    }

    public function testFallbackStrategyErrorsOutGracefullyIfMethodMissing(): void
    {
        $checker = new FeatureChecker(new ConfigFeatureStorage(['new_checkout' => false]));
        $realService = new MockCheckoutServiceEngine();
        $attribute = new Feature(name: 'new_checkout', 
            disabled: Feature::STRATEGY_FALLBACK, 
            fallback: 'nonExistentMethod'
        );
        $proxy = new FeatureProxy(fn() => $realService, $checker, $attribute);
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Fallback method "nonExistentMethod" missing');
        $proxy->process();
    }

    public function testBothStrategiesBypassAndRunRealServiceWhenEnabled(): void
    {
        $checker = new FeatureChecker(new ConfigFeatureStorage(['new_checkout' => true]));
        $realService = new MockCheckoutServiceEngine();
        $attribute = new Feature(name: 'new_checkout', 
            disabled: Feature::STRATEGY_FALLBACK, 
            fallback: 'legacyProcess'
        );
        $proxy = new FeatureProxy(fn() => $realService, $checker, $attribute);
        $this->assertSame('new_process_executed', $proxy->process());
    }
}
