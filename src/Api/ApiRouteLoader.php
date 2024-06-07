<?php

namespace Storybook\Api;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

final class ApiRouteLoader extends Loader
{
    public const NAME = 'storybook_api';

    private bool $isLoaded = false;

    public function __construct(private readonly bool $enabled, ?string $env = null)
    {
        parent::__construct($env);
    }

    public function supports(mixed $resource, ?string $type = null): bool
    {
        return self::NAME === $type;
    }

    public function load(mixed $resource, ?string $type = null): RouteCollection
    {
        if (true === $this->isLoaded) {
            throw new \RuntimeException('storybook_api route loader cannot be used more than once.');
        }

        $routes = new RouteCollection();
        dump($this->enabled);
        if ($this->enabled) {
            $route = new Route(
                '/_storybook/api/{action}',
                ['_controller' => 'storybook.controller.api'],
                ['action' => '.+'],
            );

            $routes->add('storybook_api', $route);
        }

        $this->isLoaded = true;

        return $routes;
    }
}
