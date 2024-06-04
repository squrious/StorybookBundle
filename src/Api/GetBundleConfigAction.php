<?php

namespace Storybook\Api;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/bundle-config')]
class GetBundleConfigAction extends AbstractAction
{
    public function __construct(private readonly KernelInterface $kernel)
    {
    }

    public function __invoke(Request $request): Response
    {
        $container = $this->kernel->getContainer();
//        $container->get

        return $this->json([]);
    }
}
