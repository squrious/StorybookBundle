<?php

namespace Storybook\Api;

use Symfony\Component\HttpFoundation\JsonResponse;

class AbstractAction
{
    protected function json(mixed $data): JsonResponse
    {
        $response = new JsonResponse($data, 200);
        $response->setEncodingOptions($response->getEncodingOptions() | \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE);
        return $response;
    }
}
