<?php

namespace Storybook\Exception;

interface ApiExceptionInterface extends ExceptionInterface
{
    public function getReturnCode(): int;

    public function getHttpStatusCode(): int;
}
