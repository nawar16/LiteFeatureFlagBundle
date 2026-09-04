<?php

namespace Nawar16\LiteFeatureFlagBundle\Tests\Resolver;

use Nawar16\LiteFeatureFlagBundle\Checker\FeatureChecker;
use Nawar16\LiteFeatureFlagBundle\Context\FeatureContext;
use Nawar16\LiteFeatureFlagBundle\Resolver\EnvironmentFeatureResolver;
use PHPUnit\Framework\TestCase;

class EnvironmentFeatureResolverTest extends TestCase
{
    private array $mockEnvConfig;
    protected function setUp(): void
    {
        $this->mockEnvConfig = [
            'new_checkout' => [
                'dev' => true,
                'test' => true,
                'prod' => false
            ]
        ];
    }
    public function testResolverEnablesFeatureOnMatchingEnvironment(): void
    {
        $resolver = new EnvironmentFeatureResolver($this->mockEnvConfig);
        $checker = new FeatureChecker(['new_checkout' => false], [$resolver]);
        $context = new FeatureContext(environment: 'dev');
        $this->assertTrue($checker->isEnabled('new_checkout', $context));
    }
    public function testResolverDisablesFeatureOnMatchingEnvironment(): void
    {
        $resolver = new EnvironmentFeatureResolver($this->mockEnvConfig);
        $checker = new FeatureChecker(['new_checkout' => true], [$resolver]);
        $context = new FeatureContext(environment: 'prod');
        $this->assertFalse($checker->isEnabled('new_checkout', $context));
    }
    public function testCheckerFallsBackToYamlDefaultsWhenEnvironmentIsOmitted(): void
    {
        $resolver = new EnvironmentFeatureResolver($this->mockEnvConfig);
        $checker = new FeatureChecker(['new_checkout' => true], [$resolver]);
        $context = new FeatureContext();
        $this->assertTrue($checker->isEnabled('new_checkout', $context));
    }

    public function testCheckerFallsBackToYamlDefaultsWhenEnvironmentNotConfigured(): void
    {
        $resolver = new EnvironmentFeatureResolver($this->mockEnvConfig);
        $checker = new FeatureChecker(['new_checkout' => true], [$resolver]);
        $context = new FeatureContext(environment: 'staging');
        $this->assertTrue($checker->isEnabled('new_checkout', $context));
    }
}
