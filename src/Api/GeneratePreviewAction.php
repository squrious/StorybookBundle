<?php

namespace Storybook\Api;

use Storybook\Event\GeneratePreviewEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

#[Route('/preview', name: 'preview', methods: ['GET'])]
class GeneratePreviewAction extends AbstractAction
{
    public function __construct(private readonly Environment $twig, private readonly EventDispatcherInterface $eventDispatcher)
    {
    }

    public function __invoke(): Response
    {
        $this->eventDispatcher->dispatch(new GeneratePreviewEvent());

        $content = $this->twig->render('@Storybook/preview.html.twig');

        $result = new GeneratePreviewOutput();
        $result->content = $content;

        return $this->json($result);
    }
}
