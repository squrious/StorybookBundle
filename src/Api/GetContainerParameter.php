<?php

namespace Storybook\Api;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

/**
 * @author Nicolas Rigaud <squrious@protonmail.com>
 *
 * @internal
 */
class GetContainerParameter extends AbstractAction
{
    public function __construct(private readonly ContainerBagInterface $containerBag)
    {
    }

    public static function getName(): string
    {
        return 'get-container-parameter';
    }

    public function __invoke(...$args): mixed
    {
        ['name' => $name] = $args;

        return $this->containerBag->get($name);
    }

    public function configure(Command $command): void
    {
        $command->addArgument('name', InputArgument::REQUIRED);
    }
}
