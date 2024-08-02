<?php

namespace Storybook;

use Psr\EventDispatcher\EventDispatcherInterface;
use Storybook\Event\RenderStoryEvent;
use Storybook\Exception\RenderException;
use Storybook\Exception\UnauthorizedStoryException;
use Twig\Environment;
use Twig\Error\Error;
use Twig\Loader\ArrayLoader;
use Twig\Loader\ChainLoader;
use Twig\Sandbox\SecurityError;

final class StoryRenderer
{
    public function __construct(
        private readonly Environment $twig,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function render(Story $story): string
    {
        $storyTemplateName = sprintf('story_%s.html.twig', $story->getId());

        $loader = new ChainLoader([
            new ArrayLoader([
                $story->getTemplateName() => $story->getTemplate(),
                $storyTemplateName => sprintf("{%% sandbox %%} {%%- include '%s' -%%} {%% endsandbox %%}", $story->getTemplateName()),
            ]),
            $originalLoader = $this->twig->getLoader(),
        ]);

        $this->twig->setLoader($loader);

        try {
            $this->eventDispatcher->dispatch(new RenderStoryEvent($story));
            return $this->twig->render($storyTemplateName, $story->getArgs()->toArray());
        } catch (SecurityError $th) {
            // SecurityError can actually be raised
            throw new UnauthorizedStoryException('Story contains unauthorized content', $th);
        } catch (Error $th) {
            throw new RenderException(sprintf('Story render failed: %s', $th->getMessage()), $th);
        } finally {
            // Restore original loader
            $this->twig->setLoader($originalLoader);
        }
    }
}
