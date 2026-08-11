<?php

namespace Nawar16\LiteFeatureFlagBundle;

use Nawar16\LiteFeatureFlagBundle\DependencyInjection\Compiler\FeatureProxyPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class LiteFeatureFlagBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $container->addCompilerPass(new FeatureProxyPass());
    }
}