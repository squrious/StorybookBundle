<?php

namespace Storybook\Event;

use Storybook\Story;
use Symfony\Contracts\EventDispatcher\Event;

class RenderStoryEvent extends Event
{
    public function __construct(
        public readonly Story $story,
    ) {
    }
}