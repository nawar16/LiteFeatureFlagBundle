<?php

namespace Nawar16\LiteFeatureFlagBundle\Tests\Checker;

use Nawar16\LiteFeatureFlagBundle\Checker\FeatureChecker;
use Nawar16\LiteFeatureFlagBundle\Context\FeatureContext;
use Nawar16\LiteFeatureFlagBundle\Resolver\FeatureResolverInterface;
use PHPUnit\Framework\TestCase;

class FeatureCheckerOverrideTest extends TestCase
{
    protected function tearDown(): void{unset($_ENV['FEATURE_CHECKOUT']);}
    public function testConfiguredFeatureIsEnabled(): void
    {
        $checker = new FeatureChecker(['checkout' => true]);
        $this->assertTrue($checker->isEnabled('checkout'));
    }
    public function testConfiguredFeatureIsDisabled(): void
    {
        $checker = new FeatureChecker(['checkout' => false]);
        $this->assertFalse($checker->isEnabled('checkout'));
    }
    public function testUnknownFeatureIsDisabled(): void
    {
        $checker = new FeatureChecker([]);
        $this->assertFalse($checker->isEnabled('unknown'));
    }
    public function testEnableOverride(): void
    {
        $checker = new FeatureChecker(['checkout' => false]);
        $checker->enable('checkout');
        $this->assertTrue($checker->isEnabled('checkout'));
        $this->assertTrue($checker->isOverridden('checkout'));
    }
    public function testDisableOverride(): void
    {
        $checker = new FeatureChecker(['checkout' => true]);
        $checker->disable('checkout');
        $this->assertFalse($checker->isEnabled('checkout'));
    }
    public function testResetOverride(): void
    {
        $checker = new FeatureChecker(['checkout' => true]);
        $checker->disable('checkout');
        $checker->reset('checkout');
        $this->assertTrue($checker->isEnabled('checkout'));
    }
    public function testResetAllOverrides(): void
    {
        $checker = new FeatureChecker(['a' => true, 'b' => false]);
        $checker->disable('a');
        $checker->enable('b');
        $checker->resetAll();
        $this->assertTrue($checker->isEnabled('a'));
        $this->assertFalse($checker->isEnabled('b'));
    }
    public function testOverrideWinsOverYaml(): void
    {
        $checker = new FeatureChecker(['checkout' => true]);
        $checker->disable('checkout');
        $this->assertFalse($checker->isEnabled('checkout'));
    }
    public function testOverrideWinsOverEnvironment(): void
    {
        $checker = new FeatureChecker(['checkout' => true]);
        $_ENV['FEATURE_CHECKOUT'] = 'true';
        $checker->disable('checkout');
        $this->assertFalse($checker->isEnabled('checkout')); 
    }
    public function testResolverStillWorksWithoutOverride(): void
    {
        $resolver = new class implements FeatureResolverInterface {
            public function resolve(string $f, FeatureContext $c): ?bool { return true; }
            public function priority(): int { return 1; }
        };
        $checker = new FeatureChecker(['checkout' => false]);
        $checker->setResolvers([$resolver]);
        $this->assertTrue($checker->isEnabled('checkout'));
    }
}
