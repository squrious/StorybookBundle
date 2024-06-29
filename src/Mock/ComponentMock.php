<?php

namespace Storybook\Mock;

final class ComponentMock
{
    public function __construct(
        private readonly object $provider,
        private readonly array $config,
    ) {
    }

    public function getPropertiesProxy(object $component): MockedPropertiesProxy
    {
        return new MockedPropertiesProxy($component, $this->provider, $this->config['property'] ?? []);
    }

    public function getLiveActionProxy(object $component): MockedLiveActionProxy
    {
        return new MockedLiveActionProxy($component, $this->provider, $this->config['live_action'] ?? []);
    }
}
