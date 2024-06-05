<?php

namespace Storybook\Api;

use Storybook\Exception\ApiInvalidInputException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\UX\TwigComponent\AnonymousComponent;
use Symfony\UX\TwigComponent\ComponentFactory;

/**
 * @author Nicolas Rigaud <squrious@protonmail.com>
 *
 * @internal
 */
class GetComponentClassPathAction extends AbstractAction
{
    public function __construct(private readonly ComponentFactory $componentFactory)
    {
    }

    public static function getName(): string
    {
        return 'component-class-path';
    }

    public function __invoke(...$args): array
    {
        try {
            $component = $this->componentFactory->get($args['component']);
        } catch (\InvalidArgumentException $e) {
            throw new ApiInvalidInputException($e->getMessage());
        }

        $refl = new \ReflectionClass($component);

        $res = [];

        if (AnonymousComponent::class !== $refl->getName()) {
            $res += [
                'class' => $refl->getName(),
                'path' => $refl->getFileName(),
            ];
        }

        return $res;
    }

    public function handleRequest(Request $request): array
    {
        $component = $request->query->get('component', '');

        return $this(component: $component);
    }

    public function configure(Command $command): void
    {
        $command->addArgument('component', InputArgument::REQUIRED);
    }

    public function execute(InputInterface $input): array
    {
        $component = $input->getArgument('component');

        return $this(component: $component);
    }
}
