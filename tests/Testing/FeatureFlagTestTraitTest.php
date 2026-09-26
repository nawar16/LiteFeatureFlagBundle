<?php

namespace Nawar16\LiteFeatureFlagBundle\Tests\Testing;

use Nawar16\LiteFeatureFlagBundle\Checker\FeatureChecker;
use Nawar16\LiteFeatureFlagBundle\Storage\ConfigFeatureStorage;
use Nawar16\LiteFeatureFlagBundle\Testing\FeatureFlagTestTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class FeatureFlagTestTraitTest extends KernelTestCase
{
    use FeatureFlagTestTrait;
    private FeatureChecker $mockChecker;
    protected function setUp(): void{$this->mockChecker = new FeatureChecker(new ConfigFeatureStorage(['checkout' => false]));}
    protected function getFeatureChecker(): FeatureChecker
    {
        return $this->mockChecker;
    }
    public function testEnableFeatureTraitMethod(): void
    {
        $this->enableFeature('checkout');
        $this->assertFeatureEnabled('checkout');
    }
    public function testDisableFeatureTraitMethod(): void
    {
        $this->mockChecker->enable('checkout');
        $this->disableFeature('checkout');
        $this->assertFeatureDisabled('checkout');
    }
    public function testResetFeatureTraitMethod(): void
    {
        $this->enableFeature('checkout');
        $this->resetFeature('checkout');
        $this->assertFalse($this->mockChecker->isOverridden('checkout'));
    }
    public function testResetFeaturesTraitMethod(): void
    {
        $this->enableFeature('checkout');
        $this->resetFeatures();
        $this->assertEmpty($this->mockChecker->overrides());
    }
    protected function resetFeatureFlagsAfterTest(): void
    {
        $this->resetFeatures();
    }
    public function testOverridesDoNotLeakBetweenTests(): void
    {
        $this->enableFeature('checkout');
        $this->resetFeatureFlagsAfterTest();
        $this->assertFalse($this->mockChecker->isOverridden('checkout'));
    }
}
