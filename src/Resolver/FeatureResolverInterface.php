<?php

namespace Nawar16\LiteFeatureFlagBundle\Resolver;

use Nawar16\LiteFeatureFlagBundle\Context\FeatureContext;

interface FeatureResolverInterface
{
    public function resolve(string $feature, FeatureContext $context):?bool;
}
