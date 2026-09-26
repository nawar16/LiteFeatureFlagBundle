<?php

namespace Nawar16\LiteFeatureFlagBundle\Tests\Checker;

use Nawar16\LiteFeatureFlagBundle\Checker\FeatureChecker;
use Nawar16\LiteFeatureFlagBundle\Storage\ConfigFeatureStorage;
use Nawar16\LiteFeatureFlagBundle\Storage\InMemoryFeatureStorage;
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
        $expectedData = ['checkout' => true, 'new_ui' => false];
        $storage = new ConfigFeatureStorage($expectedData);
        $checker = new FeatureChecker($storage);
        $this->assertSame($expectedData, $checker->all());
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

    public function testCheckerCanUseDifferentStorage(): void
    {
        $storage = new InMemoryFeatureStorage(['new_checkout' => true,]);
        $checker = new FeatureChecker($storage);
        self::assertTrue($checker->isEnabled('new_checkout'));
    }
    public function testUnknownFeatureIsDisabledWithInMemoryStorage(): void
    {
        $storage = new InMemoryFeatureStorage();
        $checker = new FeatureChecker($storage);
        self::assertFalse($checker->isEnabled('does_not_exist'));
    }
}
