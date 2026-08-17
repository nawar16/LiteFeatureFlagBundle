<?php

namespace Nawar16\LiteFeatureFlagBundle\Proxy;

use Nawar16\LiteFeatureFlagBundle\Attribute\Feature;
use Nawar16\LiteFeatureFlagBundle\Checker\FeatureChecker;
use Nawar16\LiteFeatureFlagBundle\Exception\FeatureDisabledException;

final class FeatureProxy
{
    private ?object $realInstance= null;

    /**
     * @param callable $decorated
     */
    public function __construct(
        private object $decorated,
        private FeatureChecker $checker,
        private Feature $attribute) 
    {}

    public function __call(string $method, array $arguments): mixed
    {
        if($this->checker->isEnabled($this->attribute->name))
            return $this->decorated->$method(...$arguments);
        if ($this->attribute->disabled === Feature::STRATEGY_FALLBACK) {
            $fallbackMethod = $this->attribute->fallback;
            if (!$fallbackMethod || !method_exists($this->decorated, $fallbackMethod)) {
                throw new \BadMethodCallException(sprintf(
                    'Fallback strategy was requested for feature "%s", but fallback method "%s" does not exist on class %s',
                    $this->attribute->name,
                    $fallbackMethod ?? 'null',
                    get_class($this->decorated)
                ));
            }
            return $this->decorated->$fallbackMethod(...$arguments);
        }
        throw FeatureDisabledException::forFeature($this->attribute->name);
    }
}
