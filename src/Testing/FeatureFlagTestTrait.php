<?php

namespace Nawar16\LiteFeatureFlagBundle\Testing;

use LogicException;
use Nawar16\LiteFeatureFlagBundle\Checker\FeatureChecker;
use Psr\Container\ContainerInterface;

trait FeatureFlagTestTrait
{
    abstract public static function assertTrue(mixed $condition, string $message = ''): void;
    abstract public static function assertFalse(mixed $condition, string $message = ''): void;
    abstract protected static function getContainer(): ContainerInterface;
    protected function enableFeature(string $feature): void
    {
        $this->getFeatureChecker()->enable($feature);
    }
    protected function disableFeature(string $feature): void
    {
        $this->getFeatureChecker()->disable($feature);
    }
    protected function resetFeature(string $feature): void
    {
        $this->getFeatureChecker()->reset($feature);
    }
    protected function resetFeatures(): void
    {
        $this->getFeatureChecker()->resetAll();
    }
    protected function assertFeatureEnabled(string $feature): void 
    {
        self::assertTrue(
            $this->getFeatureChecker()->isEnabled($feature),
            sprintf('Expected feature "%s" to be enabled.', $feature)
        );
    }
    protected function assertFeatureDisabled(string $feature): void 
    {
        self::assertFalse(
            $this->getFeatureChecker()->isEnabled($feature),
            sprintf('Expected feature "%s" to be disabled.', $feature)
        );
    }
    protected function getFeatureChecker(): FeatureChecker
    {
        if (!method_exists($this, 'getContainer')) 
            throw new LogicException('The FeatureFlagTestTrait requires a booted Symfony Kernel. Ensure your test extends KernelTestCase or WebTestCase');
        return self::getContainer()->get(FeatureChecker::class);
    }
}
