<?php

namespace Nawar16\LiteFeatureFlagBundle\Command;

use Nawar16\LiteFeatureFlagBundle\Checker\FeatureChecker;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'feature:list',
    description: 'Lists all available feature flags and their status'
)]
class FeatureListCommand extends Command
{
    public function __construct(private FeatureChecker $featureChecker) 
    {
        parent::__construct();
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $flags = $this->featureChecker->all();
        if (empty($flags)) {
            $io->warning('No feature flags are currently configured');
            return Command::SUCCESS;
        }
        $rows = [];
        foreach ($flags as $feature => $isEnabled)
            $rows[] = [$feature,$isEnabled ? 'yes' : 'no'];
        $io->table(['Feature', 'Enabled'], $rows);
        return Command::SUCCESS;
    }
}
