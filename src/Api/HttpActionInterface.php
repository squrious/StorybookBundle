<?php

namespace Storybook\Api;

use Storybook\Exception\ApiExceptionInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Nicolas Rigaud <squrious@protonmail.com>
 *
 * @internal
 */
interface HttpActionInterface extends ApiActionInterface
{
    /**
     * @throws ApiExceptionInterface
     */
    public function handleRequest(Request $request): mixed;
}
