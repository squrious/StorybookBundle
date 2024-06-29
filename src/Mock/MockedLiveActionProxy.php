<?php

namespace Storybook\Mock;

final class MockedLiveActionProxy
{
    public function __construct(private readonly object $component, private readonly object $provider, private readonly array $mockedMethods)
    {
    }

    public function getCallable(string $name): callable
    {
        if (\array_key_exists($name, $this->mockedMethods)) {
            return function (...$args) use ($name) {
                $invocationContext = new MockInvocationContext($this->component, $args);

                return $this->provider->{$this->mockedMethods[$name]}($invocationContext);
            };
        }

        if (method_exists($this->component, $name)) {
            return $this->component->{$name}(...);
        }

        throw new \LogicException('No mocked method nor original method found.');
    }
}
