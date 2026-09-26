<?php

namespace Nawar16\LiteFeatureFlagBundle\Tests\Command;

use Nawar16\LiteFeatureFlagBundle\Checker\FeatureChecker;
use Nawar16\LiteFeatureFlagBundle\Command\FeatureStatusCommand;
use Nawar16\LiteFeatureFlagBundle\Storage\ConfigFeatureStorage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class FeatureStatusCommandTest extends TestCase
{
    public function testExecuteOutputsCorrectEnabledStatus(): void
    {
        $checker = new FeatureChecker(new ConfigFeatureStorage(['checkout' => true]));
        $application = new Application();
        $application->add(new FeatureStatusCommand($checker));
        $command = $application->find('feature:status');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['name' => 'checkout']);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('checkout', $output);
        $this->assertStringContainsString('Status : ENABLED', $output);
    }
}
