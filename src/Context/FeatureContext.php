<?php

namespace Nawar16\LiteFeatureFlagBundle\Context;

final class FeatureContext
{
    public function __construct(public readonly ?string $environment = null) {}
}
