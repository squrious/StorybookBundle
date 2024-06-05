<?php

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function (ContainerConfigurator $container): void {
    $container
        ->services()
            ->defaults()
                ->private()
                ->autowire(false)
                ->autoconfigure(false)
                ->tag('storybook.api_action')
            ->set('storybook.api.generate_preview', Storybook\Api\GeneratePreviewAction::class)
                ->args([
                    0 => new Reference('storybook.twig'),
                    1 => new Reference('event_dispatcher'),
                ])
            ->set('storybook.api.bundle_config', Storybook\Api\GetBundleConfigAction::class)
                ->args([
                    0 => new Reference('kernel'),
                ])
            ->set('storybook.api.get_component_class_path', Storybook\Api\GetComponentClassPathAction::class)
                ->args([
                    0 => new Reference('ux.twig_component.component_factory')
                ])
            ->set('storybook.api.get_container_parameter', Storybook\Api\GetContainerParameter::class)
                ->args([
                    0 => new Reference('parameter_bag')
                ])
    ;
};
