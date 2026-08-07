<?php

namespace Nawar16\LiteFeatureFlagBundle\Command;

use Nawar16\LiteFeatureFlagBundle\Checker\FeatureChecker;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'feature:status',
    description: 'Displays the status of a specific feature flag'
)]
class FeatureStatusCommand extends Command
{
    public function __construct(private FeatureChecker $featureChecker) 
    {
        parent::__construct();
    }
    protected function configure(): void
    {
        $this->addArgument('name',InputArgument::REQUIRED,
            'The name of the feature flag you want to check.'
        );
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $featureName = (string) $input->getArgument('name');   
        $flags = $this->featureChecker->all();
        if (!array_key_exists($featureName, $flags)) {
            $io->error(sprintf('The feature flag "%s" is not configured.', $featureName));
            return Command::FAILURE;
        }
        $isEnabled = $this->featureChecker->isEnabled($featureName);
        $io->writeln($featureName);
        $io->newLine();
        if ($isEnabled) $io->writeln('Status : <info>ENABLED</info>');
        else $io->writeln('Status : <comment>DISABLED</comment>');
        return Command::SUCCESS;
    }
}
