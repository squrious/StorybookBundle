<?php

namespace Storybook\DependencyInjection\Compiler;

use Storybook\Api\AbstractAction;
use Storybook\Api\ApiActionInterface;
use Storybook\Api\ConsoleActionInterface;
use Storybook\Api\HttpActionInterface;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ApiActionPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $services = $container->findTaggedServiceIds('storybook.api_action');

        $actionsReferences = [];

        foreach ($services as $id => $attributes) {
            $definition = $container->getDefinition($id);
            $definitionClass = $definition->getClass();

            if (!\is_a($definitionClass, ApiActionInterface::class, true)) {
                throw new \LogicException(sprintf('API action "%s" should implement "%s" (its class is "%s").', $id, ApiActionInterface::class, $definitionClass));
            }

            $actionName = $definitionClass::getName();

            if (\is_a($definitionClass, HttpActionInterface::class, true)) {
                $actionsReferences[$actionName] = new Reference($id);
            }

            if (\is_a($definitionClass, ConsoleActionInterface::class, true)) {
                $commandDefinition = new ChildDefinition('storybook.api.abstract_command');
                $commandDefinition->replaceArgument(0, new Reference($id));
                $commandDefinition->addTag('console.command', ['name' => sprintf('storybook:api:%s', $actionName)]);

                $container->setDefinition(sprintf('storybook.api.%s_command', strtr($actionName, ['-' => '_'])), $commandDefinition);
            }
        }

        $container->getDefinition('storybook.controller.api')
            ->replaceArgument(0, ServiceLocatorTagPass::register($container, $actionsReferences));
    }
}
