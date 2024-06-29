<?php

namespace Storybook\Tests\Fixtures\Mock;

use Storybook\Attributes\AsComponentMock;
use Storybook\Attributes\LiveActionMock;
use Storybook\Mock\MockInvocationContext;
use Storybook\Tests\Fixtures\Component\LiveComponent;

#[AsComponentMock(LiveComponent::class)]
class LiveComponentMock
{
    #[LiveActionMock]
    public function mockedAction(MockInvocationContext $context)
    {
        $context->component->prop .= $context->originalArgs['value'];
        $context->component->message = 'mock called';
    }
}
