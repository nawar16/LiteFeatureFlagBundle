<?php

namespace Nawar16\LiteFeatureFlagBundle\Proxy;

use Nawar16\LiteFeatureFlagBundle\Checker\FeatureChecker;
use Nawar16\LiteFeatureFlagBundle\Exception\FeatureDisabledException;

final class FeatureProxy
{
    private ?object $realInstance= null;

    /**
     * @param callable $decorated
     */
    public function __construct(
        private $decorated,
        private FeatureChecker $checker,
        private string $feature) 
    {}

    public function __call(string $method, array $arguments): mixed
    {
        !$this->checker->isEnabled($this->feature)?
            throw FeatureDisabledException::forFeature($this->feature):'';
        $this->realInstance ===null? $this->realInstance = ($this->decorated)():'';
        return $this->realInstance->$method(...$arguments);
        //return $this->decorated->$method(...$arguments);
    }
}
