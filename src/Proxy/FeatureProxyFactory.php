<?php

namespace Nawar16\LiteFeatureFlagBundle\Proxy;

use Nawar16\LiteFeatureFlagBundle\Attribute\Feature;
use Nawar16\LiteFeatureFlagBundle\Checker\FeatureChecker;

class FeatureProxyFactory
{
    public static function createProxy(callable $realServiceInstantiator, FeatureChecker $checker, Feature $feature): FeatureProxy
    {
        return new FeatureProxy($realServiceInstantiator, $checker, $feature);
    }
}
