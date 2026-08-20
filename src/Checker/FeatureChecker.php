<?php

namespace Nawar16\LiteFeatureFlagBundle\Checker;

use Nawar16\LiteFeatureFlagBundle\Context\FeatureContext;

class FeatureChecker
{
    public function __construct(private array $flags,private array $resolvers=[]) 
    {}
    public function isEnabled(string $feature,?FeatureContext $context = null): bool
    {
        $context = $context ?? new FeatureContext();
        $envOverride = 'FEATURE_' . strtoupper($feature);
        if (isset($_ENV[$envOverride])) {
            return filter_var($_ENV[$envOverride], FILTER_VALIDATE_BOOL);
        }
        foreach ($this->resolvers as $resolver) {
            $decision = $resolver->resolve($feature, $context);
            if ($decision !== null) return $decision; 
        }
        return $this->flags[$feature] ?? false;
    }
    public function all(): array
    {
        return $this->flags;
    }
}
