<?php

namespace Storybook\Attributes;

#[\Attribute(\Attribute::TARGET_METHOD)]
final class LiveActionMock
{
    /**
     * @param string|null          $action  The live action to mock. Defaults to the method name.
     * @param string|string[]|null $stories Stories that use this mock. Pass null to use the mock in all stories.
     */
    public function __construct(
        public readonly ?string $action = null,
        public readonly string|array|null $stories = null,
    ) {
    }
}
