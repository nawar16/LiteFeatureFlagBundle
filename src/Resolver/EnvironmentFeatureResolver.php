<?php

namespace Nawar16\LiteFeatureFlagBundle\Resolver;

use Nawar16\LiteFeatureFlagBundle\Context\FeatureContext;

final class EnvironmentFeatureResolver implements FeatureResolverInterface
{
    /**
     * @param array $environmentsConfig Format: ['new_checkout' => ['dev' => true, 'prod' => false]]
     */
    public function __construct(private array $environmentsConfig) {}
    public function resolve(string $feature, FeatureContext $context): ?bool
    {
        if($context->environment === null) return null;
        if(!isset($this->environmentsConfig[$feature]))return null;
        $featureRules = $this->environmentsConfig[$feature];
        if (array_key_exists($context->environment, $featureRules))
            return (bool)$featureRules[$context->environment];
        return null;
    }
    public function priority(): int{return 200;}
}
