<?php

namespace Storybook\Api;

use Storybook\Event\GeneratePreviewEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;

/**
 * @author Nicolas Rigaud <squrious@protonmail.com>
 *
 * @internal
 */
class GeneratePreviewAction extends AbstractAction
{
    public function __construct(private readonly Environment $twig, private readonly EventDispatcherInterface $eventDispatcher)
    {
    }

    public static function getName(): string
    {
        return 'generate-preview';
    }

    public function __invoke(mixed ...$args): string
    {
        $this->eventDispatcher->dispatch(new GeneratePreviewEvent());

        return $this->twig->render('@Storybook/preview.html.twig');
    }
}
