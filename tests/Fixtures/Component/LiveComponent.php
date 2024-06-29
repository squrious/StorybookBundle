<?php

namespace Storybook\Tests\Fixtures\Component;

use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
class LiveComponent
{
    use DefaultActionTrait;

    #[LiveProp]
    public ?string $prop = null;

    public ?string $message = null;

    #[LiveAction]
    public function mockedAction(#[LiveArg] $value)
    {
        throw new \BadMethodCallException('This method should not be called.');
    }

    #[LiveAction]
    public function notMockedAction(#[LiveArg] $value): void
    {
        $this->prop .= $value;
        $this->message = 'not mock called';
    }
}
