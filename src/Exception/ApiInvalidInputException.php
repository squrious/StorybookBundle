<?php

namespace Storybook\Exception;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\HttpFoundation\Response;

final class ApiInvalidInputException extends \RuntimeException implements ApiExceptionInterface
{
    public function getReturnCode(): int
    {
        return Command::INVALID;
    }

    public function getHttpStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }
}
