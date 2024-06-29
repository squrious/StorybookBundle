<?php

namespace Storybook\Tests\Fixtures;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;

class LiveComponentKernelBrowser extends KernelBrowser
{
    protected function doRequest(object $request): Response
    {
        $request->headers->set('X-Storybook-Proxy', true);
        $request->headers->set('referer', 'http://localhost:6006?viewMode=story&id=story');

        return parent::doRequest($request);
    }
}
