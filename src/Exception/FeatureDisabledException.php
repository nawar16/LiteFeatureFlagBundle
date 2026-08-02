<?php

namespace Nawar16\LiteFeatureFlagBundle\Exception;

class FeatureDisabledException extends \RuntimeException
{
    public static function forFeature(string $featureName): self
    {
        return new self(sprintf('The feature "%s" is currently disabled.', $featureName));
    }
}
