<?php

namespace Storybook\Mock;

/**
 * @author Nicolas Rigaud <squrious@protonmail.com>
 *
 * @internal
 */
final class ComponentMockMetadata
{
    public function __construct(
        public readonly array $defaultMocks,
        public readonly array $storiesMocks,
    ) {
    }

    /**
     * Get mocks definitions for this story and append default mocks.
     *
     * @return array<string>
     */
    public function getMocksForStory(string $story): array
    {
        return ($this->storiesMocks[$story] ?? []) + ($this->defaultMocks ?? []);
    }
}
