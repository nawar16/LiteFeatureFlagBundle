<?php

use Nawar16\LiteFeatureFlagBundle\Checker\FeatureChecker;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $services->set(FeatureChecker::class)
        ->arg('$flags', '%feature_flags.flags%');
};
