<?php

namespace Storybook\Mock;

final class MockConfiguration
{
    private array $defaults = [];
    private array $storiesMocks = [];

    public function __construct(
        private readonly string $componentClass,
        private readonly string $mockClass,
    ) {
    }

    /**
     * @return array{defaults: array<string, string>, stories: array<string, array<string, string>}
     */
    public function toArray(): array
    {
        return [
            'defaults' => $this->defaults,
            'stories' => $this->storiesMocks,
        ];
    }

    public function addMock(string $originalMethod, string $targetMethod, string|array|null $stories): void
    {
        if (null === $stories) {
            if (isset($this->defaults[$targetMethod])) {
                throw new \LogicException(sprintf('Cannot mock "%s::%s" more than once in global scope (previously mocked by "%s::%s").', $this->componentClass, $originalMethod, $this->mockClass, $this->defaults[$originalMethod]));
            }

            $this->defaults[$originalMethod] = $targetMethod;
        } else {
            $stories = \is_array($stories) ? $stories : [$stories];
            foreach ($stories as $story) {
                if (isset($this->storiesMocks[$story][$originalMethod])) {
                    throw new \LogicException(sprintf('Cannot mock "%s::%s" more than once for story "%s". (previously mocked by "%s::%s").', $this->componentClass, $originalMethod, $story, $this->mockClass, $this->storiesMocks[$story][$originalMethod]));
                }

                $this->storiesMocks[$story] ??= [];

                $this->storiesMocks[$story][$originalMethod] = $targetMethod;
            }
        }
    }
}
