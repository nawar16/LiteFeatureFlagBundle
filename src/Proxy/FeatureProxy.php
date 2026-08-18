<?php

namespace Nawar16\LiteFeatureFlagBundle\Proxy;

use BadMethodCallException;
use Nawar16\LiteFeatureFlagBundle\Attribute\Feature;
use Nawar16\LiteFeatureFlagBundle\Checker\FeatureChecker;
use Nawar16\LiteFeatureFlagBundle\Exception\FeatureDisabledException;

final class FeatureProxy
{
    private ?object $realInstance = null;
    public function __construct(
        private $decorated, //callback closure
        private FeatureChecker $checker,
        private Feature $attribute
    ) {}
    public function __call(string $method, array $arguments): mixed
    {
        if (!$this->checker->isEnabled($this->attribute->name)) {
            if ($this->attribute->disabled === Feature::STRATEGY_FALLBACK) {
                $fallbackMethod = $this->attribute->fallback;
                $instance = $this->getRealInstance();
                if (!$fallbackMethod || !method_exists($instance, $fallbackMethod)) 
                    throw new BadMethodCallException(sprintf('Fallback method "%s" missing', $fallbackMethod));
                return $instance->$fallbackMethod(...$arguments);
            }
            throw FeatureDisabledException::forFeature($this->attribute->name);
        }
        return $this->getRealInstance()->$method(...$arguments);
    }

    private function getRealInstance(): object
    {
        if ($this->realInstance === null) 
            $this->realInstance = ($this->decorated)();
        return $this->realInstance;
    }
}
