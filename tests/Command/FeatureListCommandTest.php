<?php

namespace Nawar16\LiteFeatureFlagBundle\Tests\Command;

use Nawar16\LiteFeatureFlagBundle\Checker\FeatureChecker;
use Nawar16\LiteFeatureFlagBundle\Command\FeatureListCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class FeatureListCommandTest extends TestCase
{
    public function testExecuteRendersFeatureTableCorrectly(): void
    {
        $checker = new FeatureChecker(['checkout' => true,'new_ui' => false]);
        $application = new Application();
        $application->add(new FeatureListCommand($checker));
        $command = $application->find('feature:list');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Feature', $output);
        $this->assertStringContainsString('Enabled', $output);
        $this->assertStringContainsString('checkout', $output);
        $this->assertStringContainsString('yes', $output);
        $this->assertStringContainsString('new_ui', $output);
        $this->assertStringContainsString('no', $output);
    }

    public function testExecuteShowsWarningWhenNoFlagsConfigured(): void
    {
        $checker = new FeatureChecker([]);
        $application = new Application();
        $application->add(new FeatureListCommand($checker));
        $command = $application->find('feature:list');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('No feature flags are currently configured', $output);
    }
}
