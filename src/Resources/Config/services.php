<?php

use Nawar16\LiteFeatureFlagBundle\Checker\FeatureChecker;
use Nawar16\LiteFeatureFlagBundle\Command\FeatureListCommand;
use Nawar16\LiteFeatureFlagBundle\Command\FeatureStatusCommand;
use Nawar16\LiteFeatureFlagBundle\EventListener\FeatureAttributeListener;
use Nawar16\LiteFeatureFlagBundle\Resolver\EnvironmentFeatureResolver;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $services
        ->set(EnvironmentFeatureResolver::class)
        ->arg('$environmentsConfig', '%lite_feature_flags.environments%');

    $services->set(FeatureChecker::class)
        ->arg('$flags', '%lite_feature_flag.flags%')
        ->arg('$resolvers', [
            service(EnvironmentFeatureResolver::class)
        ]);
    $services->set(FeatureAttributeListener::class)
        ->arg('$featureChecker', service(FeatureChecker::class))
        ->tag('kernel.event_listener', ['event' => 'kernel.controller', 'method' => 'onKernelController']);
    $services->set(FeatureListCommand::class)
        ->arg('$featureChecker', service(FeatureChecker::class))
        ->tag('console.command');
    $services
        ->set(FeatureStatusCommand::class)
        ->arg('$featureChecker', service(FeatureChecker::class))
        ->tag('console.command');
};