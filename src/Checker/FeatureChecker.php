<?php

namespace Nawar16\LiteFeatureFlagBundle\Checker;

class FeatureChecker
{
    public function __construct(private array $flags) 
    {}
    public function isEnabled(string $feature): bool
    {
        $env = 'FEATURE_' . strtoupper($feature);
        if (isset($_ENV[$env])) return filter_var($_ENV[$env], FILTER_VALIDATE_BOOL);
        return $this->flags[$feature] ?? false;
    }
    public function all(): array
    {
        return $this->flags;
    }
}
