<?php

namespace Storybook\EventListener;

use Psr\Container\ContainerExceptionInterface;
use Storybook\Event\ComponentRenderEvent;
use Storybook\Mock\ComponentMockFactory;
use Storybook\Util\RequestAttributesHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;

/**
 * Creates mock component proxy on component render.
 *
 * @author Nicolas Rigaud <squrious@protonmail.com>
 *
 * @internal
 */
final class ComponentMockSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly ComponentMockFactory $componentMockFactory,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ComponentRenderEvent::class => ['onComponentRender', -256],
            ControllerArgumentsEvent::class => ['onKernelController', -256],
        ];
    }

    /**
     * @throws ContainerExceptionInterface
     */
    public function onComponentRender(ComponentRenderEvent $event): void
    {
        if (!($componentClass = $event->getComponentClass())) {
            // Anonymous components cannot be mocked
            return;
        }
        if ($this->componentMockFactory->componentHasMock($componentClass)) {
            $mock = $this->componentMockFactory->create($componentClass, $event->getStory());
            $variables = $event->getVariables();

            $variables = [
                ...$variables,
                'this' => $mock->getPropertiesProxy($variables['this']),
                'computed' => $mock->getPropertiesProxy($variables['computed']),
            ];

            $event->setVariables($variables);
        }
    }

    public function onKernelController(ControllerArgumentsEvent $event): void
    {
        $request = $event->getRequest();

        if (!RequestAttributesHelper::isStorybookRequest($request)) {
            return;
        }

        if (!$request->attributes->has('_live_component')) {
            return;
        }

        [$component,$action] = $event->getController();

        if ('__invoke' === $action) {
            return;
        }

        $componentClass = $component::class;

        if ($this->componentMockFactory->componentHasMock($componentClass)) {
            $storybookAttributes = RequestAttributesHelper::getStorybookAttributes($request);

            $mock = $this->componentMockFactory->create($componentClass, $storybookAttributes->story);
            $liveActionProxy = $mock->getLiveActionProxy($component);

            $event->setController($liveActionProxy->getCallable($action));
            $event->setArguments($request->attributes->get('_live_request_data')['args'] ?? []);
        }
    }
}
