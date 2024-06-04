<?php

use Storybook\Api\AbstractAction;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function (ContainerConfigurator $container): void {
    // ...
    $container
        ->services()
            ->defaults()
                ->private()
                ->autowire(false)
                ->autoconfigure(false)
            ->instanceof(AbstractAction::class)
                ->tag('controller.service_arguments')
            ->set('storybook.api.generate_preview', Storybook\Api\GeneratePreviewAction::class)
                ->args([
                    0 => new Reference('storybook.twig'),
                    1 => new Reference('event_dispatcher'),
                ])
            ->alias(Storybook\Api\GeneratePreviewAction::class, 'storybook.api.generate_preview')
                ->public()
            ->set('storybook.api.bundle_config', Storybook\Api\GetBundleConfigAction::class)
                ->args([
                    0 => new Reference('kernel'),
                ])
            ->alias(Storybook\Api\GetBundleConfigAction::class, 'storybook.api.bundle_config')
                ->public()
    ;
};
