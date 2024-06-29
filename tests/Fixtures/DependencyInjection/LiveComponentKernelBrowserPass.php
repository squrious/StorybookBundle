<?php

declare(strict_types=1);

namespace Storybook\Tests\Fixtures\DependencyInjection;

use Storybook\Tests\Fixtures\LiveComponentKernelBrowser;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class LiveComponentKernelBrowserPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $definition = new ChildDefinition('test.client');
        $definition->setClass(LiveComponentKernelBrowser::class);

        $container->setDefinition('test.live_component_client', $definition);
    }
}
