<?php

namespace Storybook\Tests\Unit\Mock;

use PHPUnit\Framework\TestCase;
use Storybook\Mock\MockedLiveActionProxy;
use Storybook\Mock\MockInvocationContext;

class MockedLiveActionProxyTest extends TestCase
{
    public function testOriginalMethodIsCalledIfNoMockIsConfigured()
    {
        $component = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['foo'])
            ->getMock();

        $provider = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['foo'])
            ->getMock();

        $mock = new MockedLiveActionProxy($component, $provider, []);

        $provider->expects($this->never())->method('foo');
        $component
            ->expects($this->once())
            ->method('foo')
            ->with('bar', 'baz')
        ;

        $mock->getCallable('foo')('bar', 'baz');
    }

    public function testMockInvocationContextReferencesOriginalComponentAndArguments()
    {
        $component = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['foo'])
            ->getMock();

        $provider = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['foo'])
            ->getMock();

        $mock = new MockedLiveActionProxy($component, $provider, ['foo' => 'foo']);

        $component->expects($this->never())->method('foo');
        $provider
            ->expects($this->once())
            ->method('foo')
            ->with($this->logicalAnd(
                $this->isInstanceOf(MockInvocationContext::class),
                $this->callback(function (MockInvocationContext $context) use ($component) {
                    $this->assertSame($component, $context->component);
                    $this->assertEquals('bar', $context->originalArgs[0]);
                    $this->assertEquals('baz', $context->originalArgs[1]);

                    return true;
                })
            ));

        $mock->getCallable('foo')('bar', 'baz');
    }
}
