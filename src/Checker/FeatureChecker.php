<?php

namespace Nawar16\LiteFeatureFlagBundle\Checker;

class FeatureChecker
{
    public function __construct(private array $flags) 
    {}
    public function isEnabled(string $feature): bool
    {
        return $this->flags[$feature] ?? false;
    }
    public function all(): array
    {
        return $this->flags;
    }
}
