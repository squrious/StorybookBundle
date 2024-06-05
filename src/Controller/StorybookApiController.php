<?php

namespace Storybook\Controller;

use Storybook\Api\HttpActionInterface;
use Storybook\Exception\ApiExceptionInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Nicolas Rigaud <squrious@protonmail.com>
 *
 * @internal
 */
final class StorybookApiController
{
    public function __construct(private readonly ServiceLocator $actions, private readonly bool $debug)
    {
    }

    public function __invoke(string $action, Request $request): Response
    {
        if (!$this->actions->has($action)) {
            return new JsonResponse([
                'error' => sprintf('Action "%s" does not exist.', $action),
            ], 404);
        }

        $actionService = $this->actions->get($action);

        try {
            if (!$actionService instanceof HttpActionInterface) {
                throw new \LogicException(sprintf('Action "%s" has no HTTP interface.', $action));
            }

            $result = $actionService->handleRequest($request);
        } catch (\Throwable $th) {
            $res = [
                'error' => $th->getMessage(),
            ];
            if ($this->debug) {
                $res += [
                    'trace' => $th->getTraceAsString()
                ];
            }

            if ($th instanceof ApiExceptionInterface) {
                $statusCode = $th->getHttpStatusCode();
            } else {
                $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
            }

            return new JsonResponse($res, $statusCode);
        }

        $response = new JsonResponse($result, 200);
        $response->setEncodingOptions($response->getEncodingOptions() | \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE);

        return $response;
    }
}
