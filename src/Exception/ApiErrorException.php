<?php

namespace Storybook\Exception;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\HttpFoundation\Response;

final class ApiErrorException extends \RuntimeException implements ApiExceptionInterface
{
    public function getReturnCode(): int
    {
        return Command::FAILURE;
    }

    public function getHttpStatusCode(): int
    {
        return Response::HTTP_INTERNAL_SERVER_ERROR;
    }
}
