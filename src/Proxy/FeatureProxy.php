<?php

namespace Nawar16\LiteFeatureFlagBundle\Proxy;

use Nawar16\LiteFeatureFlagBundle\Checker\FeatureChecker;
use Nawar16\LiteFeatureFlagBundle\Exception\FeatureDisabledException;

final class FeatureProxy
{
    public function __construct(
        private object $decorated,
        private FeatureChecker $checker,
        private string $feature) 
    {}

    public function __call(string $method, array $arguments): mixed
    {
        !$this->checker->isEnabled($this->feature)?
            throw FeatureDisabledException::forFeature($this->feature):'';
        return $this->decorated->$method(...$arguments);
    }
}
