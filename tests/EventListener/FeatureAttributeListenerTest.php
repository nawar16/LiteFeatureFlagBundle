<?php

namespace Nawar16\LiteFeatureFlagBundle\Tests\EventListener;

use Nawar16\LiteFeatureFlagBundle\Attribute\Feature;
use Nawar16\LiteFeatureFlagBundle\Checker\FeatureChecker;
use Nawar16\LiteFeatureFlagBundle\EventListener\FeatureAttributeListener;
use Nawar16\LiteFeatureFlagBundle\Exception\FeatureDisabledException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class FeatureAttributeListenerTest extends TestCase
{
    public function testListenerDoesNotBlockEnabledFeatures(): void
    {
        $checker = new FeatureChecker(['checkout' => true]);
        $listener = new FeatureAttributeListener($checker);
        $mockController = new class {#[Feature('checkout')]public function checkoutAction(): void {}};
        $event = $this->createControllerEvent([$mockController, 'checkoutAction']);
        $listener->onKernelController($event);
        $this->assertTrue(true); 
    }

    public function testListenerThrowsCustomExceptionWhenFeatureDisabled(): void
    {
        $checker = new FeatureChecker(['checkout' => false]);
        $listener = new FeatureAttributeListener($checker);
        $mockController = new class {#[Feature('checkout')]
            public function checkoutAction(): void {}
        };
        $event = $this->createControllerEvent([$mockController, 'checkoutAction']);
        $this->expectException(FeatureDisabledException::class);
        $this->expectExceptionMessage('The feature "checkout" is currently disabled.');
        $listener->onKernelController($event);
    }

    private function createControllerEvent(array $controller): ControllerEvent
    {
        return new ControllerEvent($this->createMock(HttpKernelInterface::class),
        $controller,new Request(),HttpKernelInterface::MAIN_REQUEST);
    }
}
