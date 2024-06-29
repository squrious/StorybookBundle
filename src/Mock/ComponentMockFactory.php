<?php

namespace Storybook\Mock;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

final class ComponentMockFactory
{
    /**
     * @var array<string,array<string,ComponentMockMetadata>>
     */
    private array $config = [];

    public function __construct(private readonly ContainerInterface $mockProviderLocator)
    {
    }

    public function addMockConfiguration(string $componentClass, array $config): void
    {
        if (isset($this->config[$componentClass])) {
            throw new \LogicException(sprintf('A mock configuration already exists for component "%s".', $componentClass));
        }

        $mockConfig = [];
        foreach (['property', 'live_action'] as $mockType) {
            $mockConfig[$mockType] = new ComponentMockMetadata($config[$mockType]['defaults'] ?? [], $config[$mockType]['stories'] ?? []);
        }

        $this->config[$componentClass] = $mockConfig;
    }

    public function componentHasMock(string $componentClass): bool
    {
        return \array_key_exists($componentClass, $this->config);
    }

    public function create(string $componentClass, string $story): ComponentMock
    {
        $provider = $this->getProvider($componentClass);

        $storyMockConfig = [];
        foreach ($this->config[$componentClass] as $mockType => $mockConfig) {
            $storyMockConfig[$mockType] = $mockConfig->getMocksForStory($story);
        }

        return new ComponentMock($provider, $storyMockConfig);
    }

    private function getProvider(string $componentClass): object
    {
        try {
            return $this->mockProviderLocator->get($componentClass);
        } catch (NotFoundExceptionInterface $e) {
            throw new \LogicException(sprintf('No mock provider is registered for component class "%s". Did you forget to use the #[AsComponentMock] attribute on your mock service?', $componentClass), previous: $e);
        }
    }
}
