<?php

namespace Storybook\DependencyInjection\Compiler;

use Storybook\Attributes\LiveActionMock;
use Storybook\Attributes\PropertyMock;
use Storybook\Mock\MockConfiguration;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Nicolas Rigaud <squrious@protonmail.com>
 */
final class ComponentMockPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $providers = $container->findTaggedServiceIds('storybook.component_mock');

        $mockFactoryDefinition = $container->getDefinition('storybook.mock_factory');

        $providerMap = [];
        foreach ($providers as $id => $tags) {
            $providerDefinition = $container->getDefinition($id);

            foreach ($tags as $attributes) {
                $componentClass = $attributes['component'];
                $componentDefinition = $container->getDefinition($componentClass);

                if (!$componentDefinition->hasTag('twig.component')) {
                    throw new \LogicException(sprintf('The given class "%s" does not seem to be a Twig Component. Did you forget to use the #AsTwigComponent attribute?', $componentClass));
                }

                if (isset($providerMap[$componentClass])) {
                    throw new \LogicException(sprintf('Component "%s" is already mocked by "%s" (trying to configure "%s").', $componentClass, $providerMap[$componentClass], $id));
                }

                $mockConfig = $this->extractMethodMocks($providerDefinition->getClass(), $componentClass);

                $providerMap[$componentClass] = new Reference($id);

                $mockFactoryDefinition->addMethodCall('addMockConfiguration', [$componentClass, $mockConfig]);
            }
        }

        $mockFactoryDefinition->setArgument(0, ServiceLocatorTagPass::register($container, $providerMap));
    }

    private function extractMethodMocks(string $providerClass, string $componentClass): array
    {
        $refl = new \ReflectionClass($providerClass);

        $propertyMockConfig = new MockConfiguration($componentClass, $providerClass);
        $liveActionMockConfig = new MockConfiguration($componentClass, $providerClass);

        foreach ($refl->getMethods() as $reflMethod) {
            foreach ($reflMethod->getAttributes(PropertyMock::class) as $attr) {
                /** @var PropertyMock $attrInstance */
                $attrInstance = $attr->newInstance();

                $originalMethod = $attrInstance->property ?? $reflMethod->getName();
                $targetMethod = $reflMethod->getName();
                $stories = $attrInstance->stories;

                $propertyMockConfig->addMock($originalMethod, $targetMethod, $stories);
            }

            foreach ($reflMethod->getAttributes(LiveActionMock::class) as $attr) {
                /** @var LiveActionMock $attrInstance */
                $attrInstance = $attr->newInstance();

                $originalMethod = $attrInstance->property ?? $reflMethod->getName();
                $mockedAction = $reflMethod->getName();
                $stories = $attrInstance->stories;

                $liveActionMockConfig->addMock($originalMethod, $mockedAction, $stories);
            }
        }

        return [
            'property' => $propertyMockConfig->toArray(),
            'live_action' => $liveActionMockConfig->toArray(),
        ];
    }
}
