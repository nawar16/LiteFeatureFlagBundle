<?php

namespace Nawar16\LiteFeatureFlagBundle\Tests\Checker;

use Nawar16\LiteFeatureFlagBundle\Checker\FeatureChecker;
use Nawar16\LiteFeatureFlagBundle\Storage\ConfigFeatureStorage;
use PHPUnit\Framework\TestCase;

class FeatureCheckerTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($_ENV['FEATURE_CHECKOUT'], $_ENV['FEATURE_NEW_UI'], $_ENV['FEATURE_RANDOM_FEATURE']);
        parent::tearDown();
    }
    public function testIsEnabledReturnsTrueWhenFlagIsActive(): void
    {
        $checker = new FeatureChecker(new ConfigFeatureStorage(['checkout' => true, 'new_ui' => false]));
        $this->assertTrue($checker->isEnabled('checkout'));
    }
    public function testIsEnabledReturnsFalseWhenFlagIsDisabled(): void
    {
        $checker = new FeatureChecker(new ConfigFeatureStorage(['checkout' => true, 'new_ui' => false]));
        $this->assertFalse($checker->isEnabled('new_ui'));
    }
    public function testIsEnabledReturnsFalseForUnknownFlags(): void
    {
        $checker = new FeatureChecker(new ConfigFeatureStorage(['checkout' => true]));
        $this->assertFalse($checker->isEnabled('random_feature'));
    }
    public function testAllReturnsCompleteArray(): void
    {
        $flags = new ConfigFeatureStorage(['checkout' => true, 'new_ui' => false]);
        $checker = new FeatureChecker($flags);
        $this->assertSame($flags, $checker->all());
    }
    public function testIsEnabledOverriddenByEnvVarTrue(): void
    {
        $_ENV['FEATURE_NEW_UI'] = 'true';
        $checker = new FeatureChecker(new ConfigFeatureStorage(['new_ui' => false]));
        $this->assertTrue($checker->isEnabled('new_ui'));
    }
    public function testIsEnabledOverriddenByEnvVarFalse(): void
    {
        $_ENV['FEATURE_CHECKOUT'] = 'false';
        $checker = new FeatureChecker(new ConfigFeatureStorage(['checkout' => true]));
        $this->assertFalse($checker->isEnabled('checkout'));
    }
    public function testIsEnabledEnvVarWorksForUnknownFlags(): void
    {
        $_ENV['FEATURE_RANDOM_FEATURE'] = 'true';
        $checker = new FeatureChecker(new ConfigFeatureStorage([]));
        $this->assertTrue($checker->isEnabled('random_feature'));
    }
}
