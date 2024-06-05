<?php

namespace Storybook\Api;

use Storybook\Exception\ApiExceptionInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

/**
 * @author Nicolas Rigaud <squrious@protonmail.com>
 *
 * @internal
 */
interface ConsoleActionInterface extends ApiActionInterface
{
    public function configure(Command $command): void;

    /**
     * @throws ApiExceptionInterface
     */
    public function execute(InputInterface $input): mixed;
}
