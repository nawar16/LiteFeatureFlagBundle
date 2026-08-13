<?php

namespace Nawar16\LiteFeatureFlagBundle\Tests\Proxy;

use Nawar16\LiteFeatureFlagBundle\Checker\FeatureChecker;
use Nawar16\LiteFeatureFlagBundle\Exception\FeatureDisabledException;
use Nawar16\LiteFeatureFlagBundle\Proxy\FeatureProxy;
use PHPUnit\Framework\TestCase;

class FeatureProxyTest extends TestCase
{
    public function testProxyForwardsCallWhenFeatureIsEnabled(): void
    {
        $checker = new FeatureChecker(['new_checkout' => true]);
        $realService = new class {public function process(): string {return 'processed!';}};
        $realServiceInstantiator = fn() => $realService;
        $proxy = new FeatureProxy($realServiceInstantiator, $checker, 'new_checkout');
        $this->assertSame('processed!', $proxy->process());
    }

    public function testProxyThrowsExceptionWhenFeatureIsDisabled(): void
    {
        $checker = new FeatureChecker(['new_checkout' => false]);
        $realService = new class {public function process(): string { return 'processed!';}};
        $realServiceInstantiator = fn() => $realService;
        $proxy = new FeatureProxy($realServiceInstantiator, $checker, 'new_checkout');
        $this->expectException(FeatureDisabledException::class);
        $this->expectExceptionMessage('The feature "new_checkout" is currently disabled');
        //blocked
        $proxy->process();
    }
}
