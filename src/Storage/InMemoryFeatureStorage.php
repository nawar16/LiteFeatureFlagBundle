<?php

namespace Nawar16\LiteFeatureFlagBundle\Storage;

final class InMemoryFeatureStorage implements FeatureStorageInterface
{
    public function __construct(private readonly array $flags = []) {}
    public function get(string $feature): ?bool
    {
        return $this->flags[$feature] ?? null;
    }
    public function has(string $feature): bool
    {
        return array_key_exists($feature, $this->flags);
    }
    public function all(): array
    {
        return $this->flags;
    }
}
