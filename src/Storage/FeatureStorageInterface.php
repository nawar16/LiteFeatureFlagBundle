<?php

namespace Nawar16\LiteFeatureFlagBundle\Storage;

interface FeatureStorageInterface
{
    public function get(string $feature): ?bool;
    public function has(string $feature): bool;
    public function all(): array;
}
