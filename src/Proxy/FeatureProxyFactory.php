<?php

namespace Nawar16\LiteFeatureFlagBundle\Proxy;

use Nawar16\LiteFeatureFlagBundle\Checker\FeatureChecker;

class FeatureProxyFactory
{
    public static function createProxy(object $realService, FeatureChecker $checker, string $feature): FeatureProxy
    {
        return new FeatureProxy($realService, $checker, $feature);
    }
}
