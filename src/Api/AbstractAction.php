<?php

namespace Storybook\Api;

use Storybook\Exception\ApiExceptionInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Nicolas Rigaud <squrious@protonmail.com>
 *
 * @internal
 */
abstract class AbstractAction implements HttpActionInterface, ConsoleActionInterface
{
    abstract public static function getName(): string;

    /**
     * @throws ApiExceptionInterface
     */
    abstract public function __invoke(...$args): mixed;

    /**
     * @throws ApiExceptionInterface
     */
    public function handleRequest(Request $request): mixed
    {
        return $this(...$request->query->all(), ...$request->request->all());
    }

    public function configure(Command $command): void
    {
    }

    /**
     * @throws ApiExceptionInterface
     */
    public function execute(InputInterface $input): mixed
    {
        return $this(...$input->getArguments());
    }
}
