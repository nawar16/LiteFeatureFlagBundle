<?php

namespace Nawar16\LiteFeatureFlagBundle\EventListener;

use Nawar16\LiteFeatureFlagBundle\Attribute\Feature;
use Nawar16\LiteFeatureFlagBundle\Checker\FeatureChecker;
use Nawar16\LiteFeatureFlagBundle\Exception\FeatureDisabledException;
use ReflectionMethod;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FeatureAttributeListener
{
    public function __construct(private FeatureChecker $featureChecker) 
    {}
    public function onKernelController(ControllerEvent $event): void
    {
        $controller = $event->getController();
        //not a standard array controller action
        if (!is_array($controller)) return;
        [$object, $method] = $controller;
        $reflectionMethod = new ReflectionMethod($object, $method);
        $attributes = $reflectionMethod->getAttributes(Feature::class);
        if (empty($attributes)) return;
        /** @var Feature $featureAttribute */
        $featureAttribute = $attributes[0]->newInstance();
        $featureName = $featureAttribute->name;
        if (!$this->featureChecker->isEnabled($featureName)) 
            throw FeatureDisabledException::forFeature($featureName);
    }
}
