<?php

namespace Storybook\DependencyInjection\Compiler;

use Storybook\Api\ApiActionInterface;
use Storybook\Api\ConsoleActionInterface;
use Storybook\Api\HttpActionInterface;
use Storybook\Api\StorybookApiCommand;
use Symfony\Component\DependencyInjection\Argument\AbstractArgument;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class ApiActionPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $apiMode = $container->resolveEnvPlaceholders($container->getParameter('storybook.api'), true);

        match ($apiMode) {
            'console' => $this->configureConsoleActions($container),
            'http' => $this->configureHttpActions($container),
            '' => null,
            default => throw new \LogicException(sprintf('Could not handle Storybook API mode "%s".', $apiMode)),
        };
    }

    private function configureConsoleActions(ContainerBuilder $container): void
    {
        $container->register('storybook.api.abstract_command', StorybookApiCommand::class)
            ->setAbstract(true)
            ->setArgument(0, new AbstractArgument('action'))
            ->setArgument(1, $container->getParameter('kernel.debug'))
        ;

        foreach ($this->collectActions($container) as $name => $serviceId) {
            $definition = $container->getDefinition($serviceId);
            if (is_a($definition->getClass(), ConsoleActionInterface::class, true)) {
                $commandDefinition = new ChildDefinition('storybook.api.abstract_command');
                $commandDefinition->replaceArgument(0, new Reference($serviceId));
                $commandDefinition->addTag('console.command', ['command' => sprintf('storybook:api:%s', $name)]);

                $container->setDefinition(sprintf('storybook.api.%s_command', strtr($name, ['-' => '_'])), $commandDefinition);
            }
        }
    }

    private function configureHttpActions(ContainerBuilder $container): void
    {
        $actionsMap = [];
        foreach ($this->collectActions($container) as $name => $serviceId) {
            $definition = $container->getDefinition($serviceId);
            if (is_a($definition->getClass(), HttpActionInterface::class, true)) {
                $actionsMap[$name] = new Reference($serviceId);
            }
        }

        $container->getDefinition('storybook.api.controller')
            ->replaceArgument(0, ServiceLocatorTagPass::register($container, $actionsMap));
    }

    /**
     * @return iterable<string,string>
     */
    private function collectActions(ContainerBuilder $container): iterable
    {
        $services = $container->findTaggedServiceIds('storybook.api_action');

        foreach ($services as $id => $attributes) {
            $definition = $container->getDefinition($id);
            $definitionClass = $definition->getClass();

            if (!is_a($definitionClass, ApiActionInterface::class, true)) {
                throw new \LogicException(sprintf('API action "%s" should implement "%s" (its class is "%s").', $id, ApiActionInterface::class, $definitionClass));
            }

            $actionName = $definitionClass::getName();

            yield $actionName => $id;
        }
    }
}
