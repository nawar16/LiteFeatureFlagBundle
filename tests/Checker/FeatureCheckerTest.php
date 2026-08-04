<?php

namespace Nawar16\LiteFeatureFlagBundle\Tests\Checker;

use Nawar16\LiteFeatureFlagBundle\Checker\FeatureChecker;
use PHPUnit\Framework\TestCase;

class FeatureCheckerTest extends TestCase
{
    public function testIsEnabledReturnsTrueWhenFlagIsActive(): void
    {
        $checker = new FeatureChecker(['checkout' => true, 'new_ui' => false]);
        $this->assertTrue($checker->isEnabled('checkout'));
    }

    public function testIsEnabledReturnsFalseWhenFlagIsDisabled(): void
    {
        $checker = new FeatureChecker(['checkout' => true, 'new_ui' => false]);
        $this->assertFalse($checker->isEnabled('new_ui'));
    }

    public function testIsEnabledReturnsFalseForUnknownFlags(): void
    {
        $checker = new FeatureChecker(['checkout' => true]);
        $this->assertFalse($checker->isEnabled('random_feature'));
    }

    public function testAllReturnsCompleteArray(): void
    {
        $flags = ['checkout' => true, 'new_ui' => false];
        $checker = new FeatureChecker($flags);
        $this->assertSame($flags, $checker->all());
    }
}
