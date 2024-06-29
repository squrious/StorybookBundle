<?php

namespace Storybook\Tests\Unit\EventListener;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Storybook\Event\ComponentRenderEvent;
use Storybook\EventListener\ComponentMockSubscriber;
use Storybook\Mock\ComponentMockFactory;
use Storybook\Mock\MockedPropertiesProxy;

class ComponentMockSubscriberTest extends TestCase
{
    public function testThisAndComputedAreProxied()
    {
        $locator = $this->createMock(ContainerInterface::class);
        $locator->method('get')->willReturn(new \stdClass());
        $mockFactory = new ComponentMockFactory($locator);
        $mockFactory->addMockConfiguration(
            'Component',
            [
                'property' => [
                    'stories' => [
                        'foo' => 'bar',
                    ],
                ],
            ],
        );
        $subscriber = new ComponentMockSubscriber($mockFactory);

        $variables = [
            'this' => new \stdClass(),
            'computed' => new \stdClass(),
        ];

        $event = new ComponentRenderEvent('story', 'Component', $variables);
        $subscriber->onComponentRender($event);

        $this->assertInstanceOf(MockedPropertiesProxy::class, $event->getVariables()['this']);
        $this->assertInstanceOf(MockedPropertiesProxy::class, $event->getVariables()['computed']);
    }

    public function testComponentIsNotProxiedIfNotConfigured()
    {
        $mockFactory = new ComponentMockFactory($this->createMock(ContainerInterface::class));
        $subscriber = new ComponentMockSubscriber($mockFactory);

        $variables = [
            'this' => $component = new \stdClass(),
            'computed' => $computed = new \stdClass(),
        ];

        $event = new ComponentRenderEvent('story', 'Component', $variables);
        $subscriber->onComponentRender($event);

        $this->assertSame($component, $event->getVariables()['this']);
        $this->assertSame($computed, $event->getVariables()['computed']);
    }

    public function testAnonymousComponentIsIgnored()
    {
        $mockFactory = new ComponentMockFactory($this->createMock(ContainerInterface::class));
        $subscriber = new ComponentMockSubscriber($mockFactory);

        $variables = [
            'this' => $component = new \stdClass(),
            'computed' => $computed = new \stdClass(),
        ];

        $event = new ComponentRenderEvent('story', null, $variables);
        $subscriber->onComponentRender($event);

        $this->assertSame($component, $event->getVariables()['this']);
        $this->assertSame($computed, $event->getVariables()['computed']);
    }
}
