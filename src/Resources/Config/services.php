<?php

use Nawar16\LiteFeatureFlagBundle\Checker\FeatureChecker;
use Nawar16\LiteFeatureFlagBundle\Command\FeatureListCommand;
use Nawar16\LiteFeatureFlagBundle\EventListener\FeatureAttributeListener;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $services->set(FeatureChecker::class)
        ->arg('$flags', '%lite_feature_flag.flags%');
    $services->set(FeatureAttributeListener::class)
        ->arg('$featureChecker', service(FeatureChecker::class))
        ->tag('kernel.event_listener', ['event' => 'kernel.controller', 'method' => 'onKernelController']);
    $services->set(FeatureListCommand::class)
        ->arg('$featureChecker', service(FeatureChecker::class))
        ->tag('console.command');
};